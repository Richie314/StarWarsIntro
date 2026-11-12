<?php
require __DIR__ . '/vendor/autoload.php';

use Richie314\SimpleMvc\Controllers\ErrorController;
use Richie314\SimpleMvc\Http\Method;
use Richie314\SimpleMvc\Routers\Router;
use StarWars\Controllers\{AdminController, AuthController, HomeController, OpeningController};
use StarWars\Models\Database;
use StarWars\Support\{Authentication, I18n};

session_start();
I18n::initialize();
$prefix = rtrim(getenv('SITE_PATH_PREFIX') ?: '', '/');
$secure = ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
$viewData = [
    'p' => $prefix,
    'locale' => I18n::locale(),
    't' => I18n::html(...),
    'requestPath' => $_SERVER['REQUEST_URI'],
    'requestUrl' => ($secure ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $_SERVER['REQUEST_URI'],
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
