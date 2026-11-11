<?php
declare(strict_types=1);
namespace StarWars\Support;

use Richie314\SimpleMvc\Utils\Cookie;
use StarWars\Models\{Database, User};

/** Only the database identity is persisted; names and privileges are read fresh for each request. */
final class Authentication
{
    public static function currentUser(): ?User
    {
        $id = $_SESSION['user_id'] ?? null;
        unset($_SESSION['admin']);
        if (!is_int($id) || $id < 1) {
            // Session identities must be integer database IDs, not numeric strings.
            unset($_SESSION['user_id']);
            return null;
        }
        $user = User::Load(Database::connection(), $id);
        if ($user === null) {
            unset($_SESSION['user_id']);
        }
        return $user;
    }

    public static function login(User $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->Id;
        unset($_SESSION['admin']);
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        $cookie = session_get_cookie_params();
        Cookie::DeleteWithOptions(session_name(), $cookie['path'], $cookie['domain']);
    }
}
