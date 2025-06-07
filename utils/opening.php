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
    public string $Title;
    public string $Episode;
    public ?string $Content;

    public OpeningLanguage $Language;

    public ?string $Author;
    public DateTime $Creation;
    public ?DateTime $LastEdit;
    public static function StringToLanguage(string $lang) : OpeningLanguage
    {
        switch ($lang)
        {
            case OpeningLanguage::English->value:
                return OpeningLanguage::English;
            case OpeningLanguage::Italian->value:
                return OpeningLanguage::Italian;
            default:
                throw new InvalidArgumentException(message: "Invalid language", code: 500);
        } 
    }
    function __construct(
        int $id, 
        string $title, 
        string $episode, 
        string|null $content, 
        OpeningLanguage|string $lang, 
        string|null $author,
        DateTime|string|null $creation,
        DateTime|string|null $lastEdit,
    ) {
        if (empty($title) || strlen(string: $episode) === 0)
        {
            throw new InvalidArgumentException(
                message: "Invalid params", 
                code: 500
            );
        }

        $this->ID = $id;

        if ($lang instanceof OpeningLanguage)
        {
            $this->Language = $lang;
        } elseif (is_string(value: $lang)) {
            $this->Language = $this::StringToLanguage(lang: $lang);
        } else {
            throw new InvalidArgumentException(
                message: "lang was invalid!", 
                code: 500,
            );
        }

        if (!isset($creation))
        {
            $this->Creation = new DateTime();
        } elseif ($creation instanceof DateTime) {
            $this->Creation = $creation;
        } else {
            $this->Creation = new DateTime(datetime: $creation);
        }

        if (!isset($lastEdit))
        {
            $this->LastEdit = null;
        } elseif ($lastEdit instanceof DateTime) {
            $this->LastEdit = $lastEdit;
        } else {
            $this->LastEdit = new DateTime(datetime: $lastEdit);
        }

        $this->Title = $title;
        $this->Episode = $episode;
        $this->Content = $content; // Can be null
        $this->Author = $author; // Can be null
    }

    private function isInDB(): bool
    {
        return $this->ID !== 0;
    }
    public function getCreationDate(): ?string
    {
        if (!isset($this->Creation))
            return null;
        return $this->Creation->format(format: 'Y-m-d H:i:s');
    }

    private function UploadToDb(mysqli $db): bool
    {
        $lang = $this->Language->value;
        $creation = $this->getCreationDate();
        if ($this->isInDB())
        {
            $query = 'REPLACE INTO `openings` ' .
                '(`ID`, `Title`, `Episode`, `Content`, `Language`, `Author`, `Creation`, `LastEdit`) ' .
                ' VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)';
            $params = [
                $this->ID, 
                $this->Title, 
                $this->Episode, 
                $this->Content, 
                $lang, 
                $this->Author,
                $creation,
            ];
        } else {
            $query = 'INSERT INTO `openings` ' . 
                '(`Title`, `Episode`, `Content`, `Language`, `Author`) ' .
                'VALUES (?, ?, ?, ?, ?)';
            $params = [
                $this->Title, 
                $this->Episode, 
                $this->Content, 
                $lang,
                $this->Author,
            ];
        }
        return (bool)$db->execute_query(query: $query, params: $params);
    }

    public function Upload(mysqli $db): bool
    {
        if (!$db)
        {
            throw new InvalidArgumentException(
                message: "db was not a mysqli object!", 
                code: 500,
            );
        }
        if (!$this->UploadToDb(db: $db))
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

    public static function Load(mysqli $db, int $id): ?self
    {
        if (!$db)
        {
            throw new InvalidArgumentException(
                message: "db was not a mysqli object!", 
                code: 500,
            );
        }
        $query = "SELECT * FROM `openings` WHERE `ID` = $id";
        $result = $db->query(query: $query);
        if (!$result || $result->num_rows !== 1)
        {
            return null;
        }
        $row = $result->fetch_assoc();
        return new self(
            id: (int)$row['ID'],
            title: $row['Title'],
            episode: $row['Episode'],
            content: $row['Content'],
            lang: $row['Language'],
            author: $row['Author'],
            creation: $row['Creation'],
            lastEdit: $row['LastEdit'],
        );
    }
    static public function LoadBriefOfUser(mysqli $db, string $user): array
    {
        if (!$db)
        {
            throw new InvalidArgumentException(
                message: "db was not a mysqli object!", 
                code: 500,
            );
        }
        if (empty($user))
        {
            throw new InvalidArgumentException(
                message: "Invalid user!", 
                code: 500,
            );
        }
        $query = "SELECT `ID`, `Title`, `Language` FROM `RecentOpenings` WHERE `Author` = ?";
        $result = $db->execute_query(
            query: $query, 
            params: [$user],
        );
        if (!$result || $result->num_rows === 0)
        {
            return [];
        }

        $array_to_return = [];
        while ($row = $result->fetch_assoc())
        {
            $array_to_return[] = new self(
                id: $row['ID'],
                title: $row['Title'],
                episode: '?',
                content: null,
                lang: $row['Language'],
                author: $user,
                creation: null,
                lastEdit: null,
            );
        }
        return $array_to_return;
    }

    static public function LoadOriginal(int $episode, OpeningLanguage $lang): self
    {
        if ($episode < 1 || $episode > 9)
        {
            throw new RangeException(
                message: 'Episode not found!', 
                code: 404,
        );
        }
        $lang_str = $lang->value;

        $path = "./preload/$lang_str/Data/$episode.json";
        $content = ReadFullFile(path: $path);

        $obj = json_decode(
            json: preg_replace(
                pattern: '/[[:^print:]]/', # unprintable character are removed to prevent parsing errors
                replacement: '', 
                subject: $content,
            ), 
            associative: true,
        ); 
        
        if (!isset($obj))
        {
            throw new JsonException(
                message: json_last_error_msg(), 
                code: 500,
            );
        }
        $path2 = $obj['path'];
        $body = ReadFullFile(path: "./preload/$lang_str/Body/$path2");
        return new self(
            id: 0,
            title: $obj['title'],
            episode: $obj['episode'],
            content: $body,
            lang: $lang,
            author: null, 
            creation: null, 
            lastEdit: null,
        );
    }

    public function Paragraphs():array
    {
        if (strlen(string: trim(string: $this->Content)) === 0)
        {
            return [];
        }
        $parts = explode(
            separator: "\n\n", 
            string: str_replace(search: "\r", replace: "", subject: $this->Content));

        return array_map(callback: function (string $s): string {
            return htmlspecialchars(string: $s);
        }, array: $parts);
    }
    public function getIntro():string
    {
        $DefaultIntros = [
            OpeningLanguage::Italian->value => "Tanto tempo fa in una galassia lontana,<br>lontana...",
            OpeningLanguage::English->value => "A long time ago in a galaxy far,<br>far away...",
        ];
        return $DefaultIntros[$this->Language->value];
    }
    public static function Delete(mysqli $db, int $id, string $user, bool $is_admin): bool
    {
        if (!$db)
        {
            throw new InvalidArgumentException(
                message: "db was not a mysqli object!", 
                code: 500,
            );
        }
        if ($id === 0)
        {
            return false;
        }

        // The intro can be deleted if the user owns it, if the user is an admin or if the intro is not owned
        $query = "DELETE FROM `openings` WHERE `ID` = ? AND (`Author` IS NULL OR `Author` = ? OR ?)";
        $result = $db->execute_query(
            query: $query, 
            params: [
                $id, 
                $user, 
                $is_admin ? 'TRUE' : 'FALSE',
            ],
        );
        return (bool)$result;
    }
}