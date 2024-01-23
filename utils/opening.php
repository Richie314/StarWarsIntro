<?php
include_once "./utils/string.php";
include_once "./utils/file.php";
/*
enum OpeningLanguage : string
{
    case Italian = "it";
    case English = "en";
}
*/
class OpeningLanguage
{
    static $Italian = "it";
    static $English = "en";
    public $value;
    function __construct($str)
    {
        $this->value = (string)$str;
        if ($this->value !== "it" && $this->value !== "en")
        {
            throw new InvalidArgumentException("Invalid language", 500);
        }
    }
}
class Opening
{
    public $ID;//int
    public $Title;//string
    public $Episode;//string
    public $Content;//string|null

    public OpeningLanguage $Language;

    public string|null $Author;
    public DateTime $Creation;
    public DateTime|null $LastEdit;
    function __construct(
        int $id, 
        string $title, 
        string $episode, 
        string|null $content, 
        OpeningLanguage|string $lang, 
        string|null $author,
        DateTime|string|null $creation,
        DateTime|string|null $lastEdit)
    {
        if (isEmpty($title) || isEmpty($episode))
        {
            throw new InvalidArgumentException("Invalid params", 500);
        }

        if (is_int($id) && $id !== 0)
        {
            $this->ID = (int)$id;
        } else {
            $this->ID = 0;
        }

        if ($lang instanceof OpeningLanguage)
        {
            $this->Language = $lang;
        } elseif (is_string($lang)) {
            $this->Language = new OpeningLanguage($lang);
        } else {
            throw new InvalidArgumentException("lang was invalid!", 500);
        }


        if (!isset($creation))
        {
            $this->Creation = new DateTime();
        } elseif ($creation instanceof DateTime) {
            $this->Creation = $creation;
        } else {
            $this->Creation = new DateTime($creation);
        }

        if (!isset($lastEdit))
        {
            $this->LastEdit = null;
        } elseif ($lastEdit instanceof DateTime) {
            $this->LastEdit = $lastEdit;
        } else {
            $this->LastEdit = new DateTime($lastEdit);
        }

        $this->Title = $title;
        $this->Episode = $episode;
        $this->Content = $content; // Can be null
        $this->Author = $author; // Can be null
    }

    private function isInDB() //: bool
    {
        return $this->ID !== 0;
    }
    public function getCreationDate()
    {
        if (!isset($this->Creation))
            return null;
        return $this->Creation->format('Y-m-d H:i:s');
    }

    private function PrepareUploadStatement(mysqli $db) //: mysqli_stmt
    {
        $lang = $this->Language->value;
        $creation = $this->getCreationDate();
        if ($this->isInDB())
        {
            $stmt = $db->prepare(
                'REPLACE INTO `openings` (`ID`, `Title`, `Episode`, `Content`, `Language`, `Author`, `Creation`, `LastEdit`) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)');
            if (!$stmt)
            {
                throw new UnexpectedValueException('Could not prepare the statement!', 500);
            }
            if (!$stmt->bind_param(
                'issssss', 
                $this->ID, 
                $this->Title, 
                $this->Episode, 
                $this->Content, 
                $lang, 
                $this->Author,
                $creation))
            {
                throw new UnexpectedValueException('Could not bind parameters to the statement!', 500);
            }
            return $stmt;
        } 

        $stmt = $db->prepare(
            'INSERT INTO `openings` (`Title`, `Episode`, `Content`, `Language`, `Author`) VALUES (?, ?, ?, ?, ?)');
        if (!$stmt)
        {
            throw new UnexpectedValueException('Could not prepare the statement!', 500);
        }
        if (!$stmt->bind_param(
            'sssss', 
            $this->Title, 
            $this->Episode, 
            $this->Content, 
            $lang,
            $this->Author))
        {
            throw new UnexpectedValueException('Could not bind parameters to the statement!', 500);
        }
        return $stmt;
    }

    public function Upload(mysqli $db) //: bool
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!", 500);
        }
        $stmt = $this->PrepareUploadStatement($db);
        if (!$stmt->execute() || $stmt->affected_rows === 0)
        {
            return false;
        }
        if ($this->isInDB())
        {
            $this->LastEdit = new DateTime();
        } else {
            $this->Creation = new DateTime();
        }
        $this->ID = (int)$db->insert_id;
        return true;
    }

    static public function Load(mysqli $db, int $id) // : Opening|null
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!", 500);
        }
        $query = "SELECT * FROM `openings` WHERE `ID` = $id"; // No risk since $id is an int
        $result = $db->query($query);
        if (!$result || $result->num_rows !== 1)
        {
            return null;
        }
        $row = $result->fetch_assoc();
        return new Opening(
            (int)$row['ID'],
            $row['Title'],
            $row['Episode'],
            $row['Content'],
            $row['Language'],
            $row['Author'],
            $row['Creation'],
            $row['LastEdit']
        );
    }
    static public function LoadBriefOfUser(mysqli $db, string $user) //: array
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!", 500);
        }
        if (isEmpty($user))
        {
            throw new InvalidArgumentException("Invalid user!", 500);
        }
        $query = "SELECT `ID`, `Title`, `Language` FROM `RecentOpenings` WHERE `Author` = ?";
        $result = $db->execute_query($query, array($user));
        if (!$result || $result->num_rows === 0)
        {
            return array();
        }
        $array_to_return = array();
        while ($row = $result->fetch_assoc())
        {
            $current_element = new Opening(
                $row['ID'],
                $row['Title'],
                '?',
                null,
                $row['Language'],
                $user,
                null,
                null
            );
            $array_to_return[] = $current_element;
        }
        return $array_to_return;
    }

    static public function LoadOriginal(int $episode, OpeningLanguage $lang) //: Opening
    {
        if (!is_int($episode) || $episode < 1 || $episode > 9)
        {
            throw new RangeException('Episode not found!', 404);
        }
        $lang_str = $lang->value;

        $path = "./preload/$lang_str/Data/$episode.json";
        $content = ReadFullFile($path);

        $obj = json_decode(preg_replace('/[[:^print:]]/', '', $content), true); # unprintable character are removed to prevent parsing errors
        //echo $content;
        if (!isset($obj))
        {
            throw new JsonException(json_last_error_msg(), 500);
        }
        $path2 = $obj['path'];
        $body = ReadFullFile("./preload/$lang_str/Body/$path2");
        return new Opening(
            0,
            $obj['title'],
            $obj['episode'],
            $body,
            $lang,
            null, null, null
        );
    }

    public function Paragraphs() //:array
    {
        if (isEmpty($this->Content))
        {
            return array();
        }
        $parts = explode("\n\n", str_replace("\r", "", $this->Content));
        return array_map("htmlspecialchars", $parts);
    }
    public function getIntro() //:string
    {
        $DefaultIntros = array(
            OpeningLanguage::$Italian => "Tanto tempo fa in una galassia lontana,<br>lontana...",
            OpeningLanguage::$English => "A long time ago in a galaxy far,<br>far away...",
        );
        return $DefaultIntros[$this->Language->value];
    }
    public static function Delete(mysqli $db, int $id, string $user, bool $is_admin) //:bool
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!", 500);
        }
        if ($id === 0)
        {
            return false;
        }
        // The intro can be deleted if the user owns it or if the user is an admin
        $query = "DELETE FROM `openings` WHERE `ID` = ? AND (`Author` = ? OR ?)";
        $stmt = $db->prepare($query);
        $int_bool = $is_admin ? 1 : 0;
        if (!$stmt || !$stmt->bind_param("isi", $id, $user, $int_bool))
        {
            return false;
        }
        return $stmt->execute() && $db->affected_rows === 1;
    }
}
class Report
{
    public $ID;
    public $Opening;
    public $Text;
    public $Creation;
    public $WasViewedByAdmin;
    public $IsProblematic;
    function __construct(
        int $id, 
        int $opening, 
        string $text, 
        DateTime|string $creation,
        bool $viewed = false,
        bool $problematic = false)
    {
        $this->ID = $id;
        $this->Opening = $opening;
        $this->Text = $text;
        $this->WasViewedByAdmin = $viewed;
        $this->IsProblematic = $problematic;
        if ($creation instanceof DateTime)
        {
            $this->Creation = $creation;
        } else {
            $this->Creation = new DateTime($creation);
        }
    }

    static function MakeNew(mysqli $db, int $opening, string $text) //: Report|false
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!", 500);
        }
        $stmt = $db->prepare('INSERT INTO `report` (`Opening`, `Text`) VALUES (?, ?)');
        if (!$stmt || !$stmt->bind_param('is', $opening, $text))
        {
            return false;
        }
        if (!$stmt->execute() || $stmt->affected_rows !== 1)
        {
            return false;
        }
        return new Report((int)$db->insert_id, $opening, $text, new DateTime());
    }
    static function Load(mysqli $db, int $id) //: Report|null
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!", 500);
        }
        $query = "SELECT * FROM `report` WHERE `ID` = $id";
        $result = $db->query($query);
        if (!$result || !$result->num_rows !== 1)
        {
            return null;
        }
        if (!$row = $result->fetch_assoc())
        {
            return null;
        }
        return new Report(
            $id, 
            (int)$row["Opening"],
            $row["Text"],
            $row["Creation"],
            (bool)$row["Viewed"],
            (bool)$row["Problematic"]);
    }
    static function LoadUnViewed(mysqli $db) //: array
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!", 500);
        }
        $query = "SELECT * FROM `UnviewedReports`";
        $result = $db->query($query);  
        if (!$result || $result->num_rows === 0)
        {
            return array();
        }
        $arr = array();
        while ($row = $result->fetch_assoc())
        {
            $arr[] = new Report(
                (int)$row["ID"],
                (int)$row["Opening"],
                $row["Text"],
                $row["Creation"],
                (bool)$row["Viewed"],
                (bool)$row["Problematic"]);
        }
        return $arr;
    }

    static function SetProblematic(mysqli $db, int $id) //: bool
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!", 500);
        }
        $query = "UPDATE `report` SET `Problematic` = b'1' WHERE `ID` = ?";
        $stmt = $db->prepare($query);
        if (!$stmt || !$stmt->bind_param('i', $id))
        {
            return false;
        }
        return $stmt->execute() && $stmt->affected_rows === 1;
    }
    static function SetViewed(mysqli $db, int $id) //: bool
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!", 500);
        }
        $query = "UPDATE `report` SET `Viewed` = b'1' WHERE `ID` = ?";
        $stmt = $db->prepare($query);
        if (!$stmt || !$stmt->bind_param('i', $id))
        {
            return false;
        }
        return $stmt->execute() && $stmt->affected_rows === 1;
    }
}