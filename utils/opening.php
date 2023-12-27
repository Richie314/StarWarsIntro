<?php
include_once "./utils/string.php";
include_once "./utils/file.php";
enum OpeningLanguage: string
{
    case Italian = "it";
    case English = "en";
}
class Opening
{
    public int $ID;
    public string $Intro;
    public string $Title;
    public string $Episode;
    public string|null $Content;

    public OpeningLanguage $Language;

    public string|null $Author;
    public DateTime $Creation;
    public DateTime|null $LastEdit;

    function __construct(
        int $id, 
        string $intro, 
        string $title, 
        string $episode, 
        string|null $content, 
        OpeningLanguage $lang, 
        string|null $author,
        DateTime|string|null $creation,
        DateTime|string|null $lastEdit)
    {
        if (isEmpty($intro) || isEmpty($title) || isEmpty($episode))
        {
            throw new InvalidArgumentException();
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
        } else {
            switch ($lang)
            {
                case (string)OpeningLanguage::English:
                    $this->Language = OpeningLanguage::English;
                    break;
                case (string)OpeningLanguage::Italian:
                    $this->Language = OpeningLanguage::Italian;
                    break;
                default:
                    throw new InvalidArgumentException("Invalid language");
            } 
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

        $this->Intro = $intro;
        $this->Title = $title;
        $this->Episode = $episode;
        $this->Content = $content; // Can be null
        $this->Author = $author; // Can be null
    }

    private function isInDB(): bool
    {
        return $this->ID !== 0;
    }

    private function PrepareUploadStatement(mysqli $db): mysqli_stmt
    {
        if ($this->isInDB())
        {
            $stmt = $db->prepare('REPLACE INTO `openings` (`ID`, `Intro`, `Title`, `Episode`, `Content`, `Language`, `Author`) VALUES (?, ?, ?, ?, ?, ?, CURRENT_DATETIME)');
            if (!$stmt)
            {
                throw new UnexpectedValueException('Could not prepare the statement!');
            }
            if (!$stmt->bind_param('issssss', $this->ID, $this->Intro, $this->Title, $this->Episode, $this->Content, $this->Author))
            {
                throw new UnexpectedValueException('Could not bind parameters to the statement!');
            }
            return $stmt;
        } 

        $stmt = $db->prepare('INSERT INTO `openings` (`Intro`, `Title`, `Episode`, `Content`, `Language`, `Author`) VALUES (?, ?, ?, ?, ?, ?)');
        if (!$stmt)
        {
            throw new UnexpectedValueException('Could not prepare the statement!');
        }
        if (!$stmt->bind_param('ssssss', $this->Intro, $this->Title, $this->Episode, $this->Content, $this->Author))
        {
            throw new UnexpectedValueException('Could not bind parameters to the statement!');
        }
        return $stmt;
    }

    public function Upload(mysqli $db): bool
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!");
        }
        $stmt = $this->PrepareUploadStatement($db);
        if (!$stmt->execute() || $db->affected_rows !== 1)
        {
            return false;
        }
        if ($this->isInDB())
        {
            $this->LastEdit = new DateTime();
        } else {
            $this->Creation = new DateTime();
        }
        $this->ID = $db->insert_id;
        return true;
    }

    static public function Load(mysqli $db, int $id) : Opening|null
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!");
        }
        $query = "SELECT * FROM `openings` WHERE `ID` = $id";
        $result = $db->query($query);
        if (!$result || $result->num_rows !== 1)
        {
            return null;
        }
        $row = $result->fetch_assoc();
        return new Opening(
            $row['ID'],
            $row['Intro'],
            $row['Title'],
            $row['Episode'],
            $row['Content'],
            $row['Language'],
            $row['Author'],
            $row['Creation'],
            $row['LastEdit']
        );
    }
    static public function LoadBriefOfUser(mysqli $db, string $user): array
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!");
        }
        if (isEmpty($user))
        {
            throw new InvalidArgumentException("Invalid user!");
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
                '?',
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

    static public function LoadOriginal(int $episode, OpeningLanguage $lang): Opening
    {
        if (!is_int($episode) || $episode < 1 || $episode > 9)
        {
            throw new RangeException('Episode not found!');
        }
        $lang_str = (string)$lang;

        $path = ".preload/$lang_str/$episode.json";
        $content = ReadFullFile($path);

        $obj = json_decode($content);
        $path2 = $obj['path'];
        $body = ReadFullFile($path2);
        return new Opening(
            $obj['id'],
            $obj['intro'],
            $obj['title'],
            $obj['episode'],
            $body,
            $obj['lang'],
            null, null, null
        );
    }
}