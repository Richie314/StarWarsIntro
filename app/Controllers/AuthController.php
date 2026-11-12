<?php
declare(strict_types=1);
namespace StarWars\Controllers;

use Richie314\SimpleMvc\Controllers\Controller;
use StarWars\Support\I18n;
use Richie314\SimpleMvc\Controllers\Attributes\RequireLogin;
use Richie314\SimpleMvc\Http\{Method, StatusCode};
use Richie314\SimpleMvc\Utils\Cookie;
use StarWars\Models\{Database, User};
use StarWars\Support\Authentication;

class AuthController extends Controller
{
    public function index(?string $username = null, ?string $password = null): StatusCode
    {
        if ($this->User !== null) {
            return $this->RedirectWithStatus($this->PathPrefix . '/opening', StatusCode::Found);
        }
        $error = null;
        if ($this->RequestMethod === Method::Post) {
            if ($username === null || $password === null) {
                return $this->BadRequest(I18n::text("Username and password are required."));
            }
            $db = Database::connection();
            $account = User::FindByName($db, $username);
            if ($account !== null && $account->checkPassword($password)) {
                $account->Log($db);
                Authentication::login($account);
                return $this->afterLogin();
            }
            $error = I18n::text("Invalid username or password.");
        }
        return $this->form(I18n::text("Log in"), '/auth', ['showForgotPassword' => true, 'error' => $error]);
    }

    public function register(?string $username = null, ?string $password = null, ?string $email = null): StatusCode
    {
        if ($this->User !== null) {
            return $this->RedirectWithStatus($this->PathPrefix . '/opening', StatusCode::Found);
        }
        $error = null;
        if ($this->RequestMethod === Method::Post) {
            if ($username === null || !preg_match('/^[A-Za-z0-9]{1,32}$/D', $username)
                || $password === null || strlen($password) < 8 || strlen($password) > 24
                || $email === null || strlen($email) > 128 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->BadRequest(I18n::text("Invalid registration details."));
            }
            $db = Database::connection();
            if (User::FindByName($db, $username) !== null) {
                $error = I18n::text("That username is already taken.");
            } else {
                $account = User::Create($db, $username, $password, $email);
                $account->SendWelcomeEmail();
                $account->Log($db);
                Authentication::login($account);
                return $this->afterLogin();
            }
        }
        return $this->form(I18n::text("Sign up"), '/auth/register', ['showEmail' => true, 'error' => $error]);
    }

    public function forgotPassword(?string $username = null, ?string $email = null): StatusCode
    {
        if ($this->User !== null) {
            return $this->RedirectWithStatus($this->PathPrefix . '/opening', StatusCode::Found);
        }
        $message = null;
        if ($this->RequestMethod === Method::Post) {
            if ($username === null || $email === null) {
                return $this->BadRequest(I18n::text("Username and email are required."));
            }
            $db = Database::connection();
            $account = User::FindByName($db, $username);
            $message = $account !== null && $account->Email === $email && $account->ResetPassword($db)
                ? I18n::text('Email sent to {email}', ['email' => $email]) : I18n::text("Something went wrong.");
        }
        return $this->form(I18n::text("Reset password"), '/auth/forgotPassword', [
            'showEmail' => true, 'hidePassword' => true, 'buttonLabel' => I18n::text('Send me a new password'), 'error' => $message,
        ]);
    }

    #[RequireLogin(loginPath: '/auth')]
    public function changePassword(?string $password = null, ?string $new_password = null): StatusCode
    {
        $error = null;
        if ($this->RequestMethod === Method::Post) {
            if ($password === null || $new_password === null
                || strlen($new_password) < 8 || strlen($new_password) > 24) {
                return $this->BadRequest(I18n::text("Invalid password."));
            }
            $db = $this->DB;
            $account = User::Load($db, $this->User->Id);
            if ($account === null || !$account->checkPassword($password)) {
                $error = I18n::text("Invalid password.");
            } else {
                if ($account->ChangePassword($db, $new_password)) {
                    return $this->RedirectWithStatus($this->PathPrefix . '/opening', StatusCode::SeeOther);
                }
                $error = I18n::text("Could not update your password.");
            }
        }
        return $this->form(I18n::text("Change password"), '/auth/changePassword', [
            'hideUsername' => true, 'showNewPassword' => true, 'error' => $error,
        ]);
    }

    #[RequireLogin(loginPath: '/auth')]
    public function logout(): StatusCode
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
