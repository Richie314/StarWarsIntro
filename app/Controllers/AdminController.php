<?php
declare(strict_types=1);
namespace StarWars\Controllers;

use Richie314\SimpleMvc\Controllers\Controller;
use Richie314\SimpleMvc\Controllers\Attributes\RequireLogin;
use Richie314\SimpleMvc\Http\{Method, StatusCode};
use StarWars\Models\{Login, User};

class AdminController extends Controller
{
    #[RequireLogin(requireAdmin: true, loginPath: '/auth')]
    public function index(...$extra): StatusCode
    {
        $db = $this->DB;
        return $this->Render('Pages/admin', [
            'logins' => Login::RecentLogs($db), 'inactive' => User::InactiveUsers($db), 'scripts' => ['manage.js'],
        ], title: 'Admin');
    }

    #[RequireLogin(requireAdmin: true, loginPath: '/auth')]
    public function deleteUser(mixed $id = null, ...$extra): StatusCode
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($this->RequestMethod !== Method::Post || $id === false) {
            return $this->Json(['esit' => 'errore'], StatusCode::BadRequest);
        }
        return $this->Json(['esit' => User::Delete($this->DB, $id) ? 'ok' : 'errore']);
    }
}
