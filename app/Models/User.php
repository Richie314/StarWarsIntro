<?php
declare(strict_types=1);
namespace StarWars\Models;

use InvalidArgumentException;
use Richie314\SimpleMvc\Users\User as MvcUser;
use Richie314\SimpleMvc\Utils\Security;
use StarWars\Support\Random;
use mysqli;

class User extends MvcUser
{
    private string $passwordHash;
    public string $Email;

    public function __construct(int $id, string $name, #[\SensitiveParameter] string $passwordHash, string $email, bool $admin = false)
    {
        if ($id < 1 || trim($name) === '' || $passwordHash === '') {
            throw new InvalidArgumentException('A persisted user needs an integer ID, name and password hash.');
        }
        $this->Id = $id;
        $this->Name = $name;
        $this->Admin = $admin;
        $this->passwordHash = $passwordHash;
        $this->Email = $email;
    }

    public static function fromRow(array $row): self
    {
        return new self((int)$row['Id'], $row['Name'], $row['PasswordHash'], $row['Email'] ?? '', (bool)$row['Admin']);
    }

    public function checkPassword(#[\SensitiveParameter] string $password): bool
    {
        return Security::TestPassword($password, $this->passwordHash);
    }

    public static function Load(mysqli $db, int $id): ?self
    {
        $row = $db->execute_query('SELECT * FROM `users` WHERE `Id` = ?', [$id])->fetch_assoc();
        return $row === null ? null : self::fromRow($row);
    }

    public static function FindByName(mysqli $db, string $name): ?self
    {
        $row = $db->execute_query('SELECT * FROM `users` WHERE `Name` = ?', [$name])->fetch_assoc();
        return $row === null ? null : self::fromRow($row);
    }

    public static function InactiveUsers(mysqli $db): array
    {
        return array_map(self::fromRow(...), $db->query('SELECT * FROM `InactiveUsers`')->fetch_all(MYSQLI_ASSOC));
    }

    public static function Create(mysqli $db, string $name, #[\SensitiveParameter] string $password,
        string $email, bool $admin = false): self
    {
        $hash = Security::Hash($password);
        if ($hash === '' || trim($name) === '') {
            throw new InvalidArgumentException('A name and password are required.');
        }
        $db->execute_query('INSERT INTO `users` (`Name`, `PasswordHash`, `Email`, `Admin`) VALUES (?, ?, ?, ?)',
            [$name, $hash, $email, (int)$admin]);
        return new self((int)$db->insert_id, $name, $hash, $email, $admin);
    }

    public function Update(mysqli $db): bool
    {
        return (bool)$db->execute_query('UPDATE `users` SET `Name` = ?, `Email` = ?, `Admin` = ? WHERE `Id` = ?',
            [$this->Name, $this->Email, (int)$this->Admin, $this->Id]);
    }

    public function ChangePassword(mysqli $db, #[\SensitiveParameter] string $password): bool
    {
        $hash = Security::Hash($password);
        if ($hash === '') {
            throw new InvalidArgumentException('A password is required.');
        }
        if (!$db->execute_query('UPDATE `users` SET `PasswordHash` = ? WHERE `Id` = ?', [$hash, $this->Id])) {
            return false;
        }
        $this->passwordHash = $hash;
        return true;
    }

    public function Log(mysqli $db): bool
    {
        return (bool)$db->execute_query('INSERT INTO `login` (`UserId`, `Ip`, `Device`) VALUES (?, ?, ?)',
            [$this->Id, substr(Security::GetIpAddress(allowCloudlfare: true), 0, 64),
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 256)]);
    }

    private function SendEmail(string $subject, string $message): bool
    {
        $domain = $_SERVER['SERVER_NAME'] ?? 'localhost';
        return mail($this->Email, $subject, $message, [
            'From' => "Star Wars Intro <no-reply@$domain>",
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    public function SendWelcomeEmail(): bool
    {
        return $this->SendEmail('Benvenuto', '<h1>Registrazione avvenuta</h1><p>Benvenuto e grazie per esserti registrato.<br>
            &nbsp;- Il team di Star Wars Intro</p><small>Ti preghiamo di non rispondere a questa email</small>');
    }

    public function ResetPassword(mysqli $db): bool
    {
        if ($this->Email === '') {
            return false;
        }
        $password = Random::password();
        return $this->ChangePassword($db, $password) && $this->SendEmail('Cambio password', '<h1>Cambio password</h1><p>La tua password &egrave;
            stata cambiata in:</p><pre>' . htmlspecialchars($password) . '</pre><small>Ti preghiamo di non rispondere
            a questa email e di cancellarla appena accedi all\'account</small>');
    }

    public static function Delete(mysqli $db, int $id): bool
    {
        return $db->execute_query('DELETE FROM `users` WHERE `Id` = ?', [$id]) && $db->affected_rows === 1;
    }
}
