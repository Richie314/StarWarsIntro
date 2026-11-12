<?php
declare(strict_types=1);
namespace StarWars\Controllers;

use Richie314\SimpleMvc\Controllers\Controller;
use StarWars\Support\I18n;
use Richie314\SimpleMvc\Controllers\Attributes\RequireLogin;
use Richie314\SimpleMvc\Http\{Method, StatusCode};
use StarWars\Models\{Database, Opening, OpeningLanguage};

class OpeningController extends Controller
{
    // The action query controls share and save confirmation dialogs in the browser.
    #[RequireLogin(loginPath: '/auth')]
    public function create(int $id = 0, ?string $title = null, ?string $episode = null,
        ?string $content = null, ?string $lang = null, ?string $action = null): StatusCode
    {
        if ($id < 0) {
            return $this->BadRequest(I18n::text("Invalid ID."));
        }
        $opening = $id === 0
            ? new Opening(0, I18n::text("Title here"), I18n::text("Episode X"), null, I18n::locale(), $this->User->Id, null, null)
            : Opening::Load($this->DB ?: Database::connection(), $id);
        if ($opening === null) {
            return $this->NotFound(I18n::text("Resource not found."));
        }
        if ($opening->AuthorId !== $this->User->Id && !$this->User->Admin) {
            return $this->NotAuthorized(I18n::text('You do not own this resource.'));
        }
        $error = null;
        if ($this->RequestMethod === Method::Post) {
            $language = $lang === null ? null : OpeningLanguage::tryFrom($lang);
            if ($title === null || trim($title) === '' || strlen($title) > 128
                || $episode === null || trim($episode) === '' || strlen($episode) > 64
                || strlen($content ?? '') > 4096 || $language === null) {
                return $this->BadRequest(I18n::text("Invalid intro details."));
            }
            $opening->Title = $title;
            $opening->Episode = $episode;
            $opening->Content = $content;
            $opening->Language = $language;
            if ($opening->Upload($this->DB)) {
                $query = http_build_query(['id' => $opening->Id, 'action' => $id === 0 ? 'share' : 'edited']);
                return $this->RedirectWithStatus($this->PathPrefix . '/opening/create' . "?$query", StatusCode::SeeOther);
            }
            $error = I18n::text("Could not save the intro.");
        }
        return $this->Render('Pages/create', [
            'opening' => $opening, 'error' => $error, 'scripts' => ['create.js'],
            'description' => I18n::text("Create or edit an intro."),
        ], title: $id === 0 ? I18n::text("Create intro") : I18n::text("Edit intro"));
    }

    public function view(?int $id = null, ?int $original = null, ?string $lang = null,
        ?string $title = null, ?string $episode = null, ?string $content = null): StatusCode
    {
        $lang ??= I18n::locale();
        $title ??= I18n::text('Title here');
        $episode ??= I18n::text('Episode name here');
        $content ??= I18n::text('Content here');
        $language = OpeningLanguage::tryFrom($lang);
        if ($language === null) {
            return $this->BadRequest(I18n::text("Invalid language."));
        }
        if ($id !== null) {
            if ($id < 1) {
                return $this->BadRequest(I18n::text("Invalid ID."));
            }
            $opening = Opening::Load($this->DB ?: Database::connection(), $id);
            if ($opening === null) {
                return $this->NotFound(I18n::text("Resource not found."));
            }
        } elseif ($original !== null) {
            if ($original < 1 || $original > 9) {
                return $this->NotFound(I18n::text("Resource not found."));
            }
            $opening = Opening::LoadOriginal($original, $language);
        } else {
            $opening = new Opening(0, trim($title) === '' ? I18n::text("Title here") : $title,
                trim($episode) === '' ? I18n::text("Episode name here") : $episode, $content, $language, null, null, null);
        }
        return $this->Render('Pages/view', [
            'opening' => $opening, 'language' => $opening->Language->value,
            'description' => I18n::text('Watch a custom Star Wars opening crawl.'),
        ], title: "$opening->Episode - $opening->Title",
            cutomLayout: __DIR__ . '/../Views/Shared/PlaybackLayout.php');
    }

    #[RequireLogin(loginPath: '/auth')]
    public function index(): StatusCode
    {
        return $this->Render('Pages/me', [
            'mainClass' => 'body dashboard',
            'intros' => Opening::LoadBriefOfUser($this->DB, $this->User->Id),
            'scripts' => ['manage.js'],
        ], title: I18n::text("My intros"));
    }

    #[RequireLogin(loginPath: '/auth')]
    public function delete(int $id = 0): StatusCode
    {
        if ($this->RequestMethod !== Method::Post || $id < 1) {
            return $this->Json(['esit' => 'error'], StatusCode::BadRequest);
        }
        $deleted = Opening::Delete($this->DB, $id, $this->User->Id, $this->User->Admin);
        return $this->Json(['esit' => $deleted ? 'ok' : 'error']);
    }
}
