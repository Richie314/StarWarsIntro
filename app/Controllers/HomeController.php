<?php
declare(strict_types=1);
namespace StarWars\Controllers;

use Richie314\SimpleMvc\Controllers\Controller;
use StarWars\Support\I18n;
use Richie314\SimpleMvc\Http\{Method, StatusCode};
use Richie314\SimpleMvc\Utils\Cookie;

class HomeController extends Controller
{
    public function index(mixed ...$extra): StatusCode
    {
        return $this->Render('Pages/index', title: I18n::text("Star Wars Intro Creator"));
    }

    public function error(int $code = 400, string $err = ''): StatusCode
    {
        $status = StatusCode::tryFrom($code);
        if ($status === null || !StatusCode::IsError($status)) {
            $status = StatusCode::BadRequest;
        }
        return $this->Render('Shared/Error', ['main_banner' => $err],
            title: I18n::text('Error {code}', ['code' => $status->value]), statusCode: $status);
    }

    public function acceptCookies(?string $return_to = null): StatusCode
    {
        if ($this->RequestMethod !== Method::Post) {
            return $this->BadRequest(I18n::text("Use the form to accept cookies."));
        }
        Cookie::SetWithOptions('cookie-policy', 'accepted', 90 * 86400,
            path: $this->PathPrefix ?: '/', sameSite: 'Strict',
            secure: ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        return $this->RedirectWithStatus($this->returnPath($return_to), StatusCode::SeeOther);
    }

    public function language(?string $locale = null, ?string $return_to = null): StatusCode
    {
        if ($this->RequestMethod !== Method::Post || !in_array($locale, ['en', 'it'], true)) {
            return $this->BadRequest(I18n::text('Use the form to choose a language.'));
        }
        Cookie::SetWithOptions('site-language', $locale, 365 * 86400,
            path: $this->PathPrefix ?: '/', sameSite: 'Lax', httpOnly: true,
            secure: ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        return $this->RedirectWithStatus($this->returnPath($return_to), StatusCode::SeeOther);
    }

    private function returnPath(?string $path): string
    {
        return $path !== null && str_starts_with($path, $this->PathPrefix . '/')
            && !str_starts_with($path, '//') && !preg_match('/[\\x00-\\x20\\\\]/', $path)
            ? $path : $this->PathPrefix . '/';
    }
}
