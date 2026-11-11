<?php
declare(strict_types=1);
namespace StarWars\Models;

use DateTime;
use Richie314\SimpleMvc\Users\User;
use mysqli;

class Login
{
    public DateTime $When;

    public function __construct(public int $Id, public User $User, public ?string $Email,
        DateTime|string $datetime, public string $Ip, public string $Device)
    {
        $this->When = $datetime instanceof DateTime ? $datetime : new DateTime($datetime);
    }

    public static function RecentLogs(mysqli $db): array
    {
        return array_map(static function (array $row): self {
            $user = new User();
            $user->Id = (int)$row['UserId'];
            $user->Name = $row['Name'];
            $user->Admin = (bool)$row['Admin'];
            return new self((int)$row['Id'], $user, $row['Email'], $row['When'], $row['Ip'], $row['Device']);
        }, $db->query('SELECT * FROM `RecentLogs`')->fetch_all(MYSQLI_ASSOC));
    }
}
