<?php

class User 
{
    public string $ID;
    public string $Password;
    public string $Email;
    public bool $Admin;
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
    public function checkPassword(string $plain_text_password) : bool
    {
        if (isEmpty($this->ID) || isEmpty($this->Password) || isEmpty($plain_text_password))
        {
            return false;
        }
        return password_verify($plain_text_password, $this->Password);
    }
    public static function Load(mysqli $db, string $id): User|null
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!", 500);
        }
        if (isEmpty($id))
        {
            return null;
        }
        $query = "SELECT * FROM `users` WHERE `ID` = ?";
        $result = $db->execute_query($query, array($id));
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
    public static function Create(mysqli $db, string $id, string $plain_text_password, string $email, bool $is_admin = false) : bool
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
    private function Upload(mysqli $db) : bool
    {
        $query = "INSERT INTO `users` (`ID`, `Password`, `Email`, `Admin`) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        if (!$stmt || !$stmt->bind_param('sssi', $this->ID, $this->Password, $this->Email, $is_admin))
        {
            return false;
        }
        $is_admin = $this->Admin ? 1 : 0;
        return (bool)$stmt->execute();
    }
    public function Log(mysqli $db) : bool
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
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        return $stmt->execute() && $db->affected_rows === 1;
    }
    
}
class Login
{
    public User $user;
    public DateTime $When;
    public string $Ip;
    public string $Device;
    public function __construct(
        User $user, DateTime|string $datetime, string $ip, string $dev)
    {
        if (!isset($user) || !isset($datetime))
        {
            throw new InvalidArgumentException("Invalid object", 500);
        }
        $this->user = $user;
        $this->When = $datetime instanceof DateTime ? $datetime : new DateTime($datetime);
        if (isEmpty($ip) || isEmpty($dev))
        {
            throw new InvalidArgumentException("Invalid string", 500);
        }
        $this->Ip = $ip;
        $this->Device = $dev;
    }

    public static function RecentLogs(mysqli $db): array
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