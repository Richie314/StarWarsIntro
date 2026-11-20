<?php
require __DIR__ . '/vendor/autoload.php';

use Richie314\SimpleMvc\Controllers\ErrorController;
use Richie314\SimpleMvc\Http\Method;
use Richie314\SimpleMvc\Routers\Router;
use StarWars\Controllers\{AdminController, AuthController, HomeController, OpeningController};
use StarWars\Models\Database;
use StarWars\Support\{Authentication, I18n};

$prefix = rtrim(getenv('SITE_PATH_PREFIX') ?: '', '/');
$secure = ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
session_start([
    'use_strict_mode' => 1,
    'cookie_path' => $prefix ?: '/',
    'cookie_secure' => $secure,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);
I18n::initialize();
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$csrf = $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path !== $prefix . '/opening/view') {
    $submitted = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (!is_string($submitted) || !hash_equals($csrf, $submitted)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        echo I18n::text('You are not authorized to view this resource.');
        return;
    }
    unset($_POST['csrf']);
}
if ($path === $prefix . '/auth/resetPassword') {
    header('Referrer-Policy: no-referrer');
    $uri = $path;
}
$viewData = [
    'p' => $prefix,
    'locale' => I18n::locale(),
    't' => I18n::html(...),
    'csrf' => $csrf,
    'requestPath' => $uri,
    'requestUrl' => ($secure ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $uri,
];
$router = new Router(pathPrefix: $prefix, applicationInstallationPath: __DIR__ . '/app');
foreach ($viewData as $name => $value) {
    $router->SetDefaultVariable($name, $value);
}
try {
    if ($user = Authentication::currentUser()) {
        $router->SetUser($user);
        $router->SetDbConnection(Database::connection());
    }
} catch (Throwable $error) {
    // Loading an authenticated identity can fail before dispatch; use the same MVC error view.
    error_log($error->getMessage());
    $errors = new ErrorController(
        requestPath: $_SERVER['REQUEST_URI'], requestMethod: Method::from($_SERVER['REQUEST_METHOD']),
        pathPrefix: $prefix, applicationInstallationPath: __DIR__ . '/app', defaultViewBag: $viewData,
    );
    $errors->InternalErrorHandler($error);
    return;
}
$router->AddController(HomeController::class, '/');
$router->AddController(AuthController::class, '/auth');
$router->AddController(OpeningController::class, '/opening');
$router->AddController(AdminController::class, '/admin');
$router->Dispatch($_SERVER['REQUEST_URI']);
