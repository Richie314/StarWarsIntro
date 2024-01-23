<?php

class User 
{
    public $ID;
    public $Password;
    public $Email;
    public $Admin;
    public function __construct(
        string $id,
        string $hash,
        string $email,
        bool $is_admin = false
    )
    {
        if (isEmpty($id))
        {
            throw new InvalidArgumentException("id was null!");
        }
        if (isEmpty($hash))
        {
            throw new InvalidArgumentException("hash was null!");
        }
        $this->ID = $id;
        $this->Password = $hash;
        $this->Email = $email;
        $this->Admin = $is_admin;
    }
    public function checkPassword(string $plain_text_password) //: bool
    {
        if (isEmpty($this->ID) || isEmpty($this->Password) || isEmpty($plain_text_password))
        {
            return false;
        }
        return password_verify($plain_text_password, $this->Password);
    }
    public static function Load(mysqli $db, string $id) //: User|null
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!", 500);
        }
        if (isEmpty($id))
        {
            return null;
        }
        $id_escaped = $db->real_escape_string($id); // It is secure since both $id and the db use the same charset (utf8)
        $query = "SELECT * FROM `users` WHERE `ID` = '$id_escaped'";
        $result = $db->query($query);
        if (!$result || $result->num_rows === 0)
        {
            return null;
        }
        $row = $result->fetch_assoc();
        if (!$row)
        {
            return null;
        }
        return new User(
            $row['ID'],
            $row['Password'],
            $row['Email'],
            (bool)$row['Admin']
        );
    }

    public static function InactiveUsers(mysqli $db) //: array
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!", 500);
        }
        $query = "SELECT * FROM `InactiveUsers`";
        $result = $db->query($query);
        if (!$result || $result->num_rows === 0)
        {
            return array();
        }
        $user_array = array();
        while ($row = $result->fetch_assoc())
        {
            $user_array[] = new User($row["ID"], '?', isset($row["Email"]) ? $row["Email"] : "");
        }
        return $user_array;
    }

    public static function Create(
        mysqli $db, 
        string $id, 
        string $plain_text_password, 
        string $email, 
        bool $is_admin = false) //: bool
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!", 500);
        }
        if (isEmpty($plain_text_password))
        {
            throw new InvalidArgumentException("password can't be null!", 500);
        }
        $hash = password_hash($plain_text_password, PASSWORD_BCRYPT);
        $user = new User($id, $hash, $email, $is_admin);
        return $user->Upload($db);
    }

    private function Upload(mysqli $db) //: bool
    {
        $query = "INSERT INTO `users` (`ID`, `Password`, `Email`, `Admin`) VALUES (?, ?, ?, ?)";
        return $this->UploadOrUpdate($db, $query);
    }
    public function Update(mysqli $db) //: bool
    {
        $query = "REPLACE INTO `users` (`ID`, `Password`, `Email`, `Admin`) VALUES (?, ?, ?, ?)";
        return $this->UploadOrUpdate($db, $query);
    }
    private function UploadOrUpdate(mysqli $db, string $query) //: bool
    {
        $stmt = $db->prepare($query);
        if (!$stmt || !$stmt->bind_param('sssi', $this->ID, $this->Password, $this->Email, $is_admin))
        {
            return false;
        }
        $is_admin = $this->Admin ? 1 : 0;
        return (bool)$stmt->execute() && $stmt->affected_rows >= 1;
    }
    public function Log(mysqli $db) //: bool
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!", 500);
        }
        if (isEmpty($this->ID))
        {
            throw new BadFunctionCallException("ID was not set!", 500);
        }
        $query = "INSERT INTO `login` (`User`, `When`, `Ip`, `Device`) VALUES (?, CURRENT_TIMESTAMP, ?, ?)";
        $stmt = $db->prepare($query);
        if (!$stmt || !$stmt->bind_param('sss', $this->ID, $ip, $user_agent))
        {
            return false;
        }
        $ip = getUserIP();
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        return $stmt->execute() && $db->affected_rows === 1;
    }

    private function SendEmail($subject, $message) //: bool
    {
        // Will fail on localhost

        $domain = $_SERVER['SERVER_NAME'];
        $headers_array = array(
            "From: Star Wars Intro <no-reply@$domain>",
            "X-Mailer: PHP/" . phpversion(),
            "Content-Type: text/html; charset=UTF-8"
        );
        $headers = join("\r\n", $headers_array);
        try {
            return mail($this->Email, $subject, $message, $headers);
        } catch (Exception $ex) {
            return false;
        }
    }
    public function SendWelcomeEmail() //: bool
    {
        $subject = "Benvenuto";
        $message = 
            "<h1>Registrazione avvenuta</h1>" .
            "<br>" .
            "<p>" .
                "Benvenuto e grazie per esserti registrato.<br>" .
                "&nbsp;- Il team di Star Wars Intro" .
            "</p>" .
            "<small>Ti preghiamo di non rispondere a questa email</small>";
        return $this->SendEmail($subject, $message);
    }
    public function ResetPassword(mysqli $db) //: bool
    {
        if (isEmpty($this->ID))
        {
            throw new BadFunctionCallException("ID was not set!", 500);
        }
        if (isEmpty($this->Email))
        {
            throw new BadFunctionCallException("Email mancante nell'account!", 500);
        }
        
        require_once "./utils/random.php";

        $new_password = random_password(12);
        $new_password_encoded = htmlspecialchars($new_password);

        $this->Password = password_hash($new_password, PASSWORD_BCRYPT);

        if (!$this->Update($db))
        {
            return false;
        }

        $subject = "Cambio password";
        $message = 
            "<h1>Cambio password</h1>" .
            "<br>" .
            "<p>" .
                "La tua password &egrave; stata cambiata in:<br>" .
                "<pre>$new_password_encoded</pre>" .
            "</p>" .
            "<small>Ti preghiamo di non rispondere a questa email e di cancellarla appena accedi all'account</small>";
        return $this->SendEmail($subject, $message);
    }
    public function SafeID() //:string
    {
        return htmlspecialchars(str_replace(array("'", "\"", "\r", "\n", "\t"), "", $this->ID));
    }
    public static function Delete(mysqli $db, string $username) //: bool
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!", 500);
        }
        if (isEmpty($username))
        {
            throw new InvalidArgumentException("username can't be empty!", 500); 
        }
        $query = "DELETE FROM `users` WHERE `ID` = ?";
        $stmt = $db->prepare($query);
        if (!$stmt || !$stmt->bind_param("s", $username))
        {
            return false;
        }
        return (bool)$stmt->execute() && $stmt->affected_rows === 1;
    }
}
class Login
{
    public $User;
    public $When;
    public $Ip;
    public $Device;
    public function __construct(
        User $user, DateTime|string $datetime, string $ip, string $dev)
    {
        if (!isset($user) || !isset($datetime))
        {
            throw new InvalidArgumentException("Invalid object", 500);
        }
        $this->User = $user;
        $this->When = $datetime instanceof DateTime ? $datetime : new DateTime($datetime);
        if (isEmpty($ip) || isEmpty($dev))
        {
            throw new InvalidArgumentException("Invalid string", 500);
        }
        $this->Ip = $ip;
        $this->Device = $dev;
    }

    public static function RecentLogs(mysqli $db) //: array
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!", 500);
        }
        $query = "SELECT * FROM `RecentLogs`";
        $result = $db->query($query);
        if (!$result || $result->num_rows === 0)
        {
            return array();
        }
        $arr = array();
        while ($row = $result->fetch_assoc())
        {
            $user = new User($row["User"], "?", $row["Email"], (bool)$row["Admin"]);
            $arr[] = new Login($user, $row["When"], $row["Ip"], $row["Device"]);
        }
        return $arr;
    }
}