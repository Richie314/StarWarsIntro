<?php
declare(strict_types=1);
namespace StarWars\Controllers;

use Richie314\SimpleMvc\Controllers\Controller;
use Richie314\SimpleMvc\Http\{Method, StatusCode};
use Richie314\SimpleMvc\Utils\Cookie;

class HomeController extends Controller
{
    public function index(...$extra): StatusCode
    {
        return $this->Render('Pages/index', title: 'Creatore Intro di Star Wars');
    }

    public function error(mixed $code = 400, mixed $err = '', ...$extra): StatusCode
    {
        $status = is_scalar($code) ? StatusCode::tryFrom((int)$code) : null;
        if ($status === null || !StatusCode::IsError($status)) {
            $status = StatusCode::BadRequest;
        }
        return $this->Render('Shared/Error', ['main_banner' => is_string($err) ? $err : 'Errore'],
            title: "Errore {$status->value}", statusCode: $status);
    }

    public function acceptCookies(mixed $return_to = null, ...$extra): StatusCode
    {
        if ($this->RequestMethod !== Method::Post) {
            return $this->BadRequest('Usa il modulo per accettare i cookie.');
        }
        Cookie::SetWithOptions('cookie-policy', 'accepted', 90 * 86400,
            path: $this->PathPrefix ?: '/', sameSite: 'Strict',
            secure: ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        $target = is_string($return_to) && str_starts_with($return_to, $this->PathPrefix . '/')
            && !str_starts_with($return_to, '//') && !preg_match('/[\x00-\x20\\\\]/', $return_to)
            ? $return_to : $this->PathPrefix . '/';
        return $this->RedirectWithStatus($target, StatusCode::SeeOther);
    }
}
