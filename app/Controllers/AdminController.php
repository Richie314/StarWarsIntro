<?php
declare(strict_types=1);
namespace StarWars\Controllers;

use Richie314\SimpleMvc\Controllers\Controller;
use StarWars\Support\I18n;
use Richie314\SimpleMvc\Controllers\Attributes\RequireLogin;
use Richie314\SimpleMvc\Http\{Method, StatusCode};
use StarWars\Models\{Login, User};

class AdminController extends Controller
{
    #[RequireLogin(requireAdmin: true, loginPath: '/auth')]
    public function index(): StatusCode
    {
        $db = $this->DB;
        return $this->Render('Pages/admin', [
            'logins' => Login::RecentLogs($db), 'inactive' => User::InactiveUsers($db), 'scripts' => ['manage.js'],
        ], title: I18n::text('Admin'));
    }

    #[RequireLogin(requireAdmin: true, loginPath: '/auth')]
    public function deleteUser(int $id = 0): StatusCode
    {
        if ($this->RequestMethod !== Method::Post || $id < 1) {
            return $this->Json(['esit' => 'errore'], StatusCode::BadRequest);
        }
        return $this->Json(['esit' => User::Delete($this->DB, $id) ? 'ok' : 'errore']);
    }
}
