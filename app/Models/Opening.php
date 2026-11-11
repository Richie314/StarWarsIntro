<?php
declare(strict_types=1);
namespace StarWars\Models;

use DateTime;
use InvalidArgumentException;
use RangeException;
use Richie314\SimpleMvc\Utils\File;
use RuntimeException;
use mysqli;

class Opening
{
    public int $Id;
    public string $Title;
    public string $Episode;
    public ?string $Content;
    public OpeningLanguage $Language;
    public ?int $AuthorId;
    public ?string $AuthorName;
    public DateTime $Creation;
    public ?DateTime $LastEdit;

    public function __construct(int $id, string $title, string $episode, ?string $content,
        OpeningLanguage|string $lang, ?int $authorId, DateTime|string|null $creation, DateTime|string|null $lastEdit,
        ?string $authorName = null)
    {
        if ($title === '' || $episode === '') {
            throw new InvalidArgumentException('Title and episode are required.');
        }
        $this->Id = $id;
        $this->Title = $title;
        $this->Episode = $episode;
        $this->Content = $content;
        $this->Language = $lang instanceof OpeningLanguage ? $lang : self::StringToLanguage($lang);
        $this->AuthorId = $authorId;
        $this->AuthorName = $authorName;
        $this->Creation = $creation instanceof DateTime ? $creation : new DateTime($creation ?? 'now');
        $this->LastEdit = $lastEdit instanceof DateTime ? $lastEdit : ($lastEdit === null ? null : new DateTime($lastEdit));
    }

    public static function StringToLanguage(string $lang): OpeningLanguage
    {
        return OpeningLanguage::tryFrom($lang) ?? throw new InvalidArgumentException('Invalid language.');
    }

    public function Upload(mysqli $db): bool
    {
        $params = [$this->Title, $this->Episode, $this->Content, $this->Language->value, $this->AuthorId];
        if ($this->Id === 0) {
            $saved = $db->execute_query('INSERT INTO `openings` (`Title`, `Episode`, `Content`, `Language`, `AuthorId`)
                VALUES (?, ?, ?, ?, ?)', $params);
            if ($saved) {
                $this->Id = $db->insert_id;
                $this->Creation = new DateTime();
            }
        } else {
            $saved = $db->execute_query('UPDATE `openings` SET `Title` = ?, `Episode` = ?, `Content` = ?,
                `Language` = ?, `AuthorId` = ?, `LastEdit` = CURRENT_TIMESTAMP WHERE `Id` = ?', [...$params, $this->Id]);
            if ($saved) {
                $this->LastEdit = new DateTime();
            }
        }
        return (bool)$saved;
    }

    public static function Load(mysqli $db, int $id): ?self
    {
        $row = $db->execute_query('SELECT * FROM `RecentOpenings` WHERE `Id` = ?', [$id])->fetch_assoc();
        return $row === null ? null : self::fromRow($row);
    }

    public static function LoadBriefOfUser(mysqli $db, int $user): array
    {
        $rows = $db->execute_query('SELECT * FROM `RecentOpenings` WHERE `AuthorId` = ?', [$user]);
        return array_map(self::fromRow(...), $rows->fetch_all(MYSQLI_ASSOC));
    }

    private static function fromRow(array $row): self
    {
        return new self((int)$row['Id'], $row['Title'], $row['Episode'], $row['Content'],
            $row['Language'], $row['AuthorId'] === null ? null : (int)$row['AuthorId'],
            $row['Creation'], $row['LastEdit'], $row['AuthorName']);
    }

    public static function LoadOriginal(int $episode, OpeningLanguage $lang): self
    {
        if ($episode < 1 || $episode > 9) {
            throw new RangeException('Episode not found.', 404);
        }
        $path = dirname(__DIR__, 2) . "/preload/{$lang->value}/$episode.json";
        if (!File::Exists($path)) {
            throw new RuntimeException('Original intro not found.', 404);
        }
        $data = json_decode(file_get_contents($path), associative: true, flags: JSON_THROW_ON_ERROR);
        return new self(0, $data['title'], $data['episode'], implode("\n", $data['content']), $lang, null, null, null);
    }

    public function Paragraphs(): array
    {
        if (trim($this->Content ?? '') === '') {
            return [];
        }
        return array_map(htmlspecialchars(...), explode("\n\n", str_replace("\r", '', $this->Content)));
    }

    public function getIntro(): string
    {
        return $this->Language === OpeningLanguage::Italian
            ? 'Tanto tempo fa in una galassia lontana,<br>lontana...'
            : 'A long time ago in a galaxy far,<br>far away...';
    }

    public static function Delete(mysqli $db, int $id, int $user, bool $is_admin): bool
    {
        $deleted = $db->execute_query('DELETE FROM `openings` WHERE `Id` = ?
            AND (`AuthorId` IS NULL OR `AuthorId` = ? OR ?)', [$id, $user, (int)$is_admin]);
        return $deleted && $db->affected_rows === 1;
    }
}
