<?php
declare(strict_types=1);
// Creates and drops uniquely named databases. Run only in an isolated test container.
require dirname(__DIR__) . '/vendor/autoload.php';

use Richie314\SimpleMvc\Users\User as MvcUser;
use StarWars\Models\{Database, Login, Opening, User};
use StarWars\Support\Authentication;

if (getenv('RUN_DATABASE_TESTS') !== '1') {
    throw new RuntimeException('Set RUN_DATABASE_TESTS=1 only for an isolated test database server.');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
session_start();
$rootPassword = getenv('TEST_MYSQL_ROOT_PASSWORD') ?: 'starwars';
$admin = new mysqli(getenv('MYSQL_HOST') ?: 'mysql', 'root', $rootPassword);
$admin->set_charset('utf8mb4');
$suffix = bin2hex(random_bytes(6));
$databaseName = 'swi_test_' . $suffix;
$databases = [];

function verify(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}
function executeSql(mysqli $db, string $sql): void
{
    $db->multi_query($sql);
    do {
        if ($result = $db->store_result()) {
            $result->free();
        }
        if (!$db->more_results()) {
            break;
        }
    } while ($db->next_result());
}
try {
    $admin->query("CREATE DATABASE `$databaseName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $databases[] = $databaseName;
    $db = new mysqli(getenv('MYSQL_HOST') ?: 'mysql', 'root', $rootPassword, $databaseName);
    $db->set_charset('utf8mb4');
    executeSql($db, file_get_contents(dirname(__DIR__) . '/db/init.sql'));
    User::Create($db, 'Luke', 'FixturePassword123', 'luke@example.test');
    User::Create($db, 'Leia', 'FixturePassword123', 'leia@example.test', true);
    User::Create($db, '42', 'FixturePassword123', 'numeric@example.test');
    $foreignKeys = $admin->execute_query("SELECT REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL
        AND TABLE_NAME IN ('login', 'openings')", [$databaseName])->fetch_all(MYSQLI_ASSOC);
    verify(count($foreignKeys) === 2, 'Missing user foreign keys.');
    foreach ($foreignKeys as $fk) {
        verify($fk['REFERENCED_TABLE_NAME'] === 'users' && $fk['REFERENCED_COLUMN_NAME'] === 'Id', 'Invalid user reference.');
    }
    $luke = User::FindByName($db, 'Luke');
    $leia = User::FindByName($db, 'Leia');
    verify($luke instanceof MvcUser && is_int($luke->Id) && $luke->Name === 'Luke' && !$luke->Admin, 'User contract mismatch.');
    verify($leia->Admin && $luke->checkPassword('FixturePassword123'), 'Role or password contract mismatch.');
    $hash = $db->query("SELECT PasswordHash FROM users WHERE Name = 'Luke'")->fetch_row()[0];
    foreach (['Id', 'Name', 'Admin'] as $property) {
        verify((new ReflectionProperty(User::class, $property))->getDeclaringClass()->getName() === MvcUser::class,
            "Identity property was redeclared: $property");
    }
    verify(!str_contains(json_encode($luke), $hash), 'Password hash exposed through the user object.');
    $opening = new Opening(0, 'Test intro', 'Episode X', "First paragraph\r\n\r\nSecond paragraph", 'en', $luke->Id, null, null);
    $opening->Upload($db);
    $openingId = $opening->Id;
    $loaded = Opening::Load($db, $openingId);
    verify($loaded->AuthorId === $luke->Id && $loaded->AuthorName === 'Luke', 'Invalid intro ownership.');
    verify($loaded->Content === $opening->Content, 'Intro text changed.');
    $numeric = User::Create($db, (string)$luke->Id, 'NumericPassword123', 'numeric@example.test');
    verify($numeric->Id !== $luke->Id && User::FindByName($db, (string)$luke->Id)->Id === $numeric->Id,
        'A numeric username was confused with a user ID.');
    verify(!Opening::Delete($db, $openingId, $numeric->Id, false), 'A numeric username bypassed ownership.');
    $luke->Name = 'LukeRenamed';
    $luke->Update($db);
    verify(Opening::Load($db, $openingId)->AuthorName === 'LukeRenamed' && Opening::Load($db, $openingId)->AuthorId === $luke->Id,
        'Renaming a user changed identity or ownership.');
    $luke->ChangePassword($db, 'ChangedPassword123');
    verify(User::Load($db, $luke->Id)->checkPassword('ChangedPassword123') && !$luke->checkPassword('FixturePassword123'),
        'Password update failed.');
    $before = (int)$db->execute_query('SELECT COUNT(*) FROM login WHERE UserId = ?', [$luke->Id])->fetch_row()[0];
    $luke->Log($db);
    $luke->Log($db);
    verify((int)$db->execute_query('SELECT COUNT(*) FROM login WHERE UserId = ?', [$luke->Id])->fetch_row()[0] === $before + 2,
        'Rapid login records were collapsed.');
    foreach (User::InactiveUsers($db) as $inactive) {
        verify($inactive instanceof MvcUser && $inactive->Id > 0 && $inactive->Name !== '', 'Invalid inactive user object.');
    }
    // Use the isolated schema for the session checks as well.
    putenv('MYSQL_DATABASE_NAME=' . $databaseName);
    putenv('MYSQL_USER=root');
    putenv('MYSQL_PASSWORD=' . $rootPassword);
    $_SESSION = ['user_id' => (string)$luke->Id, 'admin' => true];
    verify(Authentication::currentUser() === null, 'Numeric string session became an integer identity.');
    Authentication::login($leia);
    verify($_SESSION === ['user_id' => $leia->Id], 'Session stores more than the integer identity.');
    verify(Authentication::currentUser()->Admin, 'Real admin role was not loaded.');
    $db->execute_query('UPDATE users SET Admin = FALSE WHERE Id = ?', [$leia->Id]);
    verify(!Authentication::currentUser()->Admin, 'Admin revocation was cached in the session.');
    User::Delete($db, $leia->Id);
    verify(Authentication::currentUser() === null, 'Deleted user remained authenticated.');
    User::Delete($db, $luke->Id);
    verify(Opening::Load($db, $openingId)->AuthorId === null && Opening::Load($db, $openingId)->AuthorName === null, 'Author deletion did not clear ownership.');
    verify((int)$db->execute_query('SELECT COUNT(*) FROM login WHERE UserId = ?', [$luke->Id])->fetch_row()[0] === 0,
        'User login records did not cascade on deletion.');
    // Reject invalid references and non-boolean role values at the schema boundary.
    foreach (['INSERT INTO login (UserId, Ip, Device) VALUES (4294967295, "127.0.0.1", "Invalid")',
        'UPDATE users SET Admin = 2'] as $invalid) {
        try {
            $db->query($invalid);
            throw new RuntimeException('Invalid database row was accepted.');
        } catch (mysqli_sql_exception) {
        }
    }
    echo "Fresh schema, MVC user contract, integer ownership, sessions, roles and FK behavior verified.\n";
} finally {
    foreach (array_reverse($databases) as $name) {
        $admin->query("DROP DATABASE `$name`");
    }
}
