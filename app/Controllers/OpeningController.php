<?php
declare(strict_types=1);
namespace StarWars\Controllers;

use Richie314\SimpleMvc\Controllers\Controller;
use Richie314\SimpleMvc\Controllers\Attributes\RequireLogin;
use Richie314\SimpleMvc\Http\{Method, StatusCode};
use StarWars\Models\{Database, Opening, OpeningLanguage};

class OpeningController extends Controller
{
    #[RequireLogin(loginPath: '/auth')]
    public function create(mixed $id = null, mixed $title = null, mixed $episode = null,
        mixed $content = null, mixed $lang = null, ...$extra): StatusCode
    {
        $id = $id === null ? 0 : filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($id === false) {
            return $this->BadRequest('ID non valido.');
        }
        $opening = $id === 0
            ? new Opening(0, 'Titolo qui', 'Episodio X', null, 'it', $this->User->Id, null, null)
            : Opening::Load($this->DB ?: Database::connection(), $id);
        if ($opening === null) {
            return $this->NotFound('Risorsa non trovata.');
        }
        if ($opening->AuthorId !== $this->User->Id && !$this->User->Admin) {
            return $this->NotAuthorized('Non possiedi la risorsa.');
        }
        $error = null;
        if ($this->RequestMethod === Method::Post) {
            $language = is_string($lang) ? OpeningLanguage::tryFrom($lang) : null;
            if (!is_string($title) || trim($title) === '' || strlen($title) > 128
                || !is_string($episode) || trim($episode) === '' || strlen($episode) > 64
                || ($content !== null && !is_string($content)) || strlen($content ?? '') > 4096 || $language === null) {
                return $this->BadRequest('Dati della intro non validi.');
            }
            $opening->Title = $title;
            $opening->Episode = $episode;
            $opening->Content = $content;
            $opening->Language = $language;
            if ($opening->Upload($this->DB)) {
                $query = http_build_query(['id' => $opening->Id, 'action' => $id === 0 ? 'share' : 'edited']);
                return $this->RedirectWithStatus($this->PathPrefix . '/opening/create' . "?$query", StatusCode::SeeOther);
            }
            $error = 'Impossibile salvare la risorsa.';
        }
        return $this->Render('Pages/create', [
            'opening' => $opening, 'error' => $error, 'scripts' => ['create.js'],
            'description' => 'Crea nuova o modifica una intro.',
        ], title: $id === 0 ? 'Crea nuova intro' : 'Modifica intro');
    }

    public function view(mixed $id = null, mixed $original = null, mixed $lang = 'it',
        mixed $title = 'Titolo qui', mixed $episode = 'Nome episodio qui', mixed $content = 'Contenuto qui', ...$extra): StatusCode
    {
        $language = is_string($lang) ? OpeningLanguage::tryFrom($lang) : null;
        if ($language === null) {
            return $this->BadRequest('Lingua non valida.');
        }
        if ($id !== null) {
            $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) {
                return $this->BadRequest('ID non valido.');
            }
            $opening = Opening::Load($this->DB ?: Database::connection(), $id);
            if ($opening === null) {
                return $this->NotFound('Risorsa non trovata.');
            }
        } elseif ($original !== null) {
            $number = filter_var($original, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 9]]);
            if ($number === false) {
                return $this->NotFound('Risorsa non trovata.');
            }
            $opening = Opening::LoadOriginal($number, $language);
        } else {
            if (!is_string($title) || !is_string($episode) || !is_string($content)) {
                return $this->BadRequest('Dati della intro non validi.');
            }
            $opening = new Opening(0, trim($title) === '' ? 'Titolo qui' : $title,
                trim($episode) === '' ? 'Nome episodio qui' : $episode, $content, $language, null, null, null);
        }
        return $this->Render('Pages/view', [
            'opening' => $opening, 'language' => $opening->Language->value,
            'description' => "Guarda l'opening personalizzata di Star Wars",
        ], title: "$opening->Episode - $opening->Title",
            cutomLayout: __DIR__ . '/../Views/Shared/PlaybackLayout.php');
    }

    #[RequireLogin(loginPath: '/auth')]
    public function index(...$extra): StatusCode
    {
        return $this->Render('Pages/me', [
            'intros' => Opening::LoadBriefOfUser($this->DB, $this->User->Id),
            'scripts' => ['manage.js'],
        ], title: 'Le mie intro');
    }

    #[RequireLogin(loginPath: '/auth')]
    public function delete(mixed $id = null, ...$extra): StatusCode
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($this->RequestMethod !== Method::Post || $id === false) {
            return $this->Json(['esit' => 'error'], StatusCode::BadRequest);
        }
        $deleted = Opening::Delete($this->DB, $id, $this->User->Id, $this->User->Admin);
        return $this->Json(['esit' => $deleted ? 'ok' : 'error']);
    }
}
