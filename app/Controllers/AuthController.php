<?php
declare(strict_types=1);
namespace StarWars\Controllers;

use Richie314\SimpleMvc\Controllers\Controller;
use Richie314\SimpleMvc\Controllers\Attributes\RequireLogin;
use Richie314\SimpleMvc\Http\{Method, StatusCode};
use Richie314\SimpleMvc\Utils\Cookie;
use StarWars\Models\{Database, User};
use StarWars\Support\Authentication;

class AuthController extends Controller
{
    public function index(mixed $username = null, mixed $password = null, ...$extra): StatusCode
    {
        if ($this->User !== null) {
            return $this->RedirectWithStatus($this->PathPrefix . '/opening', StatusCode::Found);
        }
        $error = null;
        if ($this->RequestMethod === Method::Post) {
            if (!is_string($username) || !is_string($password)) {
                return $this->BadRequest('Username e password richiesti.');
            }
            $db = Database::connection();
            $account = User::FindByName($db, $username);
            if ($account !== null && $account->checkPassword($password)) {
                $account->Log($db);
                Authentication::login($account);
                return $this->afterLogin();
            }
            $error = 'Username o password non valide.';
        }
        return $this->form('Login', '/auth', ['showForgotPassword' => true, 'error' => $error]);
    }

    public function register(mixed $username = null, mixed $password = null, mixed $email = null, ...$extra): StatusCode
    {
        if ($this->User !== null) {
            return $this->RedirectWithStatus($this->PathPrefix . '/opening', StatusCode::Found);
        }
        $error = null;
        if ($this->RequestMethod === Method::Post) {
            if (!is_string($username) || !preg_match('/^[A-Za-z0-9]{1,32}$/D', $username)
                || !is_string($password) || strlen($password) < 8 || strlen($password) > 24
                || !is_string($email) || strlen($email) > 128 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->BadRequest('Dati di registrazione non validi.');
            }
            $db = Database::connection();
            if (User::FindByName($db, $username) !== null) {
                $error = 'Username già in uso.';
            } else {
                $account = User::Create($db, $username, $password, $email);
                $account->SendWelcomeEmail();
                $account->Log($db);
                Authentication::login($account);
                return $this->afterLogin();
            }
        }
        return $this->form('Registrati', '/auth/register', ['showEmail' => true, 'error' => $error]);
    }

    public function forgotPassword(mixed $username = null, mixed $email = null, ...$extra): StatusCode
    {
        if ($this->User !== null) {
            return $this->RedirectWithStatus($this->PathPrefix . '/opening', StatusCode::Found);
        }
        $message = null;
        if ($this->RequestMethod === Method::Post) {
            if (!is_string($username) || !is_string($email)) {
                return $this->BadRequest('Username ed email richiesti.');
            }
            $db = Database::connection();
            $account = User::FindByName($db, $username);
            $message = $account !== null && $account->Email === $email && $account->ResetPassword($db)
                ? "Email inviata a $email" : 'Qualcosa non ha funzionato.';
        }
        return $this->form('Resetta password', '/auth/forgotPassword', [
            'showEmail' => true, 'hidePassword' => true, 'buttonLabel' => 'Inviami nuova password', 'error' => $message,
        ]);
    }

    #[RequireLogin(loginPath: '/auth')]
    public function changePassword(mixed $password = null, mixed $new_password = null, ...$extra): StatusCode
    {
        $error = null;
        if ($this->RequestMethod === Method::Post) {
            if (!is_string($password) || !is_string($new_password)
                || strlen($new_password) < 8 || strlen($new_password) > 24) {
                return $this->BadRequest('Password non valida.');
            }
            $db = $this->DB;
            $account = User::Load($db, $this->User->Id);
            if ($account === null || !$account->checkPassword($password)) {
                $error = 'Password non valida!';
            } else {
                if ($account->ChangePassword($db, $new_password)) {
                    return $this->RedirectWithStatus($this->PathPrefix . '/opening', StatusCode::SeeOther);
                }
                $error = 'Impossibile aggiornare la password.';
            }
        }
        return $this->form('Cambia password', '/auth/changePassword', [
            'hideUsername' => true, 'showNewPassword' => true, 'error' => $error,
        ]);
    }

    #[RequireLogin(loginPath: '/auth')]
    public function logout(...$extra): StatusCode
    {
        Authentication::logout();
        return $this->RedirectWithStatus($this->PathPrefix . '/', StatusCode::SeeOther);
    }

    private function form(string $title, string $action, array $options = []): StatusCode
    {
        return $this->Render('Shared/form', $options + ['formAction' => $action, 'mainClass' => 'body center'], title: $title);
    }

    private function afterLogin(): StatusCode
    {
        $target = Cookie::Get('Redirect');
        if (!is_string($target) || !str_starts_with($target, $this->PathPrefix . '/')
            || str_starts_with($target, '//') || preg_match('/[\x00-\x20\\\\]/', $target)) {
            $target = $this->PathPrefix . '/opening';
        }
        // An earlier visit to the login form should not send a newly signed-in user back there.
        if (in_array(parse_url($target, PHP_URL_PATH), [$this->PathPrefix . '/auth', $this->PathPrefix . '/auth/index'], true)) {
            $target = $this->PathPrefix . '/opening';
        }
        Cookie::DeleteWithOptions('Redirect', $this->PathPrefix ?: '/');
        return $this->RedirectWithStatus($target, StatusCode::SeeOther);
    }
}
