<?php
declare(strict_types=1);
// An existing integer-ID session must fail closed without exposing connection details.
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'localhost';
session_id('swi-bootstrap-' . bin2hex(random_bytes(8)));
session_start();
$_SESSION['user_id'] = 1;
session_write_close();
putenv('MYSQL_HOST=');
ob_start();
require dirname(__DIR__) . '/index.php';
$html = ob_get_clean();
if (http_response_code() !== 500 || !str_contains($html, 'Errore interno del server.')
    || str_contains($html, 'Database configuration is missing') || str_contains($html, 'Stack trace')) {
    throw new RuntimeException('Authenticated bootstrap failure was not handled by the MVC error view.');
}
session_destroy();
echo "Database failure during session loading produces the MVC error page without exposing details.\n";
