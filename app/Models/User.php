<?php
declare(strict_types=1);
namespace StarWars\Models;

use InvalidArgumentException;
use PHPMailer\PHPMailer\{Exception as MailException, PHPMailer};
use Richie314\SimpleMvc\Users\User as MvcUser;
use Richie314\SimpleMvc\Utils\Security;
use StarWars\Support\I18n;
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
        if (!$db->execute_query('UPDATE `users` SET `PasswordHash` = ?, `ResetTokenHash` = NULL, `ResetExpiresAt` = NULL WHERE `Id` = ?', [$hash, $this->Id])) {
            return false;
        }
        $this->passwordHash = $hash;
        return true;
    }

    public function Log(mysqli $db): bool
    {
        return (bool)$db->execute_query('INSERT INTO `login` (`UserId`, `Ip`, `Device`) VALUES (?, ?, ?)',
            [$this->Id, substr(Security::GetIpAddress(allowCloudFlare: true), 0, 64),
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 256)]);
    }

    private function SendEmail(string $subject, string $message, string $plainText): bool
    {
        $host = getenv('SMTP_HOST') ?: '';
        $from = getenv('SMTP_FROM') ?: '';
        if ($host === '' || $from === '') {
            return false;
        }
        $encryption = getenv('SMTP_ENCRYPTION') ?: PHPMailer::ENCRYPTION_STARTTLS;
        $username = getenv('SMTP_USERNAME') ?: '';
        if (!in_array($encryption, [PHPMailer::ENCRYPTION_STARTTLS, PHPMailer::ENCRYPTION_SMTPS, 'none'], true)
            || ($encryption === 'none' && $username !== '')) {
            error_log('Invalid SMTP encryption configuration.');
            return false;
        }
        $defaultPort = match ($encryption) {
            PHPMailer::ENCRYPTION_SMTPS => 465,
            PHPMailer::ENCRYPTION_STARTTLS => 587,
            default => 25,
        };
        try {
            $mailer = new PHPMailer(true);
            $mailer->isSMTP();
            $mailer->Host = $host;
            $mailer->Port = (int)(getenv('SMTP_PORT') ?: $defaultPort);
            $mailer->SMTPSecure = $encryption === 'none' ? '' : $encryption;
            $mailer->SMTPAutoTLS = $encryption !== 'none';
            $mailer->SMTPAuth = $username !== '';
            $mailer->Username = $username;
            $mailer->Password = getenv('SMTP_PASSWORD') ?: '';
            $mailer->Timeout = 5;
            $mailer->CharSet = PHPMailer::CHARSET_UTF8;
            $mailer->setFrom($from, 'Star Wars Intro');
            $mailer->addAddress($this->Email);
            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $message;
            $mailer->AltBody = $plainText;
            return $mailer->send();
        } catch (MailException) {
            error_log('Star Wars Intro could not deliver email over SMTP.');
            return false;
        }
    }

    public function SendWelcomeEmail(): bool
    {
        return $this->SendEmail(I18n::text('Welcome'), '<h1>' . I18n::html('Registration complete') . '</h1><p>'
            . I18n::html('Welcome, and thank you for signing up.') . '<br>— ' . I18n::html('The Star Wars Intro team')
            . '</p><small>' . I18n::html('Please do not reply to this email.') . '</small>',
            I18n::text('Welcome, and thank you for signing up.'));
    }

    public function SendPasswordReset(mysqli $db, string $resetUrl): bool
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token, true);
        $db->execute_query('UPDATE `users` SET `ResetTokenHash` = ?,
            `ResetExpiresAt` = CURRENT_TIMESTAMP + INTERVAL 30 MINUTE WHERE `Id` = ?', [$hash, $this->Id]);
        if ($db->affected_rows !== 1) {
            return false;
        }
        $link = $resetUrl . '?token=' . $token;
        if ($this->SendEmail(I18n::text('Reset password'), '<p><a href="' . htmlspecialchars($link) . '">'
            . I18n::html('Reset password') . '</a></p>', I18n::text('Reset password') . "\n" . $link)) {
            return true;
        }
        $db->execute_query('UPDATE `users` SET `ResetTokenHash` = NULL, `ResetExpiresAt` = NULL
            WHERE `Id` = ? AND `ResetTokenHash` = ?', [$this->Id, $hash]);
        return false;
    }

    public static function RedeemPasswordReset(mysqli $db, string $token, #[\SensitiveParameter] string $password): bool
    {
        $db->execute_query('UPDATE `users` SET `PasswordHash` = ?, `ResetTokenHash` = NULL, `ResetExpiresAt` = NULL
            WHERE `ResetTokenHash` = ? AND `ResetExpiresAt` > CURRENT_TIMESTAMP',
            [Security::Hash($password), hash('sha256', $token, true)]);
        return $db->affected_rows === 1;
    }

    public static function DeleteInactive(mysqli $db, int $id): bool
    {
        return $db->execute_query('DELETE FROM `users` WHERE `Id` = ?
            AND `Admin` = FALSE AND NOT EXISTS (
                SELECT 1 FROM `login` WHERE `UserId` = ? AND `When` >= CURRENT_TIMESTAMP - INTERVAL 366 DAY
            )', [$id, $id]) && $db->affected_rows === 1;
    }

    public static function Delete(mysqli $db, int $id): bool
    {
        return $db->execute_query('DELETE FROM `users` WHERE `Id` = ?', [$id]) && $db->affected_rows === 1;
    }
}
