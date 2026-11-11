# Star Wars Intro

A PHP and MySQL application for creating and sharing custom Star Wars opening crawls. Users can register, write their own intros, preview them in a browser, and share a link. The animation uses CSS and JavaScript.

The interface is in Italian. Original movie intros are available in Italian and English.

## Run with Docker Compose

Install Docker and Docker Compose v2, then run:

```sh
docker compose up --build -d starwars
```

Open <http://localhost:8080>. Set `HTTP_PORT` in your shell or a Compose `.env` file to change the HTTP port.

Compose starts two application services:

- `starwars`: builds the single `starwars-intro:latest` image containing Nginx, PHP 8.4-FPM, mysqli, Xdebug, the site, and Composer dependencies. Compose maps host port **8080** to container port **80**.
- `mysql`: runs MariaDB and automatically imports `db/init.sql` on an empty database.

The single `docker-compose.yml` includes Xdebug by default. Database data persists in the `db-data` Docker volume. Site files and dependencies are baked into the image, with no application bind mounts or dependency volumes. The default local database name, username, and password are all `starwars`.

An optional standalone phpMyAdmin tool is available on <http://localhost:8081>:

```sh
docker compose --profile database-tools up -d phpmyadmin
```

Change `PHPMYADMIN_PORT` to choose another port. Stop the services without deleting their data:

```sh
docker compose stop
```

## Debug PHP in VS Code

1. Open the repository folder and install the recommended [PHP Debug extension](https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug).
2. Set a breakpoint in `app/Controllers/HomeController.php`.
3. Select **Docker: Start and debug PHP** in **Run and Debug**, then press **F5**.
4. Open <http://localhost:8080/?XDEBUG_TRIGGER=1>. Add `XDEBUG_TRIGGER=1` to another page's query string or use an Xdebug browser extension to trigger further requests.

The prerequisite VS Code task builds and starts the combined application container and MariaDB. Xdebug connects back to the host on port **9003** through `host.docker.internal`; the extra host mapping supports Linux Docker Engine. Container path `/starwars` maps to the open workspace.

The debugger maps the baked `/starwars` source tree to the workspace. Rebuild after changing PHP, assets, or dependencies so breakpoints match the running code. Host PHP and Composer installations are not required.

Available **Terminal → Run Task** actions:

- **Docker: Build and start**: build and start the combined image and MariaDB.
- **Docker: Stop**: stop containers while preserving data.

Use **Docker: Listen for PHP debugger** to attach to an already running stack. Stopping the debugger leaves the containers running. There is one Compose configuration for both regular browsing and debugging; Xdebug activates only when a request contains a trigger.

After changing source files, assets, or dependencies, rebuild the application image:

```sh
docker compose up --build -d starwars
```

The Dockerfile produces one application image. Supervisor starts Nginx and PHP-FPM, forwards their logs, restarts failed processes, and shuts them down when the container stops. Nginx serves assets directly and talks to PHP-FPM through `/run/php/php-fpm.sock`. The image health check verifies an HTTP request through both processes.

To build the image independently of Compose:

```sh
docker build -t starwars-intro:latest .
```

## Architecture and routes

The project uses [`richie314/simple-mvc`](https://packagist.org/packages/richie314/simple-mvc), PHP >= 8.3, and the **`StarWars`** namespace mapped to **`app/`**.

`index.php` registers controllers with the library's `AddController()`:

```php
$router->AddController(HomeController::class, '/');
$router->AddController(AuthController::class, '/auth');
$router->AddController(OpeningController::class, '/opening');
$router->AddController(AdminController::class, '/admin');
```

Each base path maps to the controller's `index()` action. Other public action names are appended verbatim, including capitalization. For example:

| Path | Purpose |
| --- | --- |
| `/` | Home |
| `/auth` | Login form and submission |
| `/auth/register` | Register |
| `/auth/forgotPassword` | Reset password |
| `/auth/changePassword` | Change password |
| `/auth/logout` | End the session |
| `/opening` | Current user's intros |
| `/opening/create` | Create or edit an intro |
| `/opening/view` | Original intro, saved intro, or preview |
| `/opening/delete` | Delete an intro via POST |
| `/admin` | Administration |
| `/admin/deleteUser` | Delete a user via POST |
| `/acceptCookies` | Store cookie consent via POST |
| `/error` | Error page |

Links use hardcoded paths with the installation prefix: `$p . '/opening/view'` in templates and `$this->PathPrefix . '/opening/view'` in controllers. There is no route registry or PHP static-file router. Shared legacy `/view.php?...` links redirect to `/opening/view?...` in Nginx.

- `app/Controllers/`: request handling and library response methods.
- `app/Models/`: users, login records, intros, and the database connection.
- `app/Views/Pages/`: page content.
- `app/Views/Shared/Layout.php`: shared document, metadata, navigation, main container, cookie notice, stars, footer, and page scripts.
- `app/Views/Shared/PlaybackLayout.php`: playback document without the site's navigation or footer.
- `app/Support/`: integer-ID session persistence and the PSR-4 autoloaded `Random` class.
- `assets/`: public stylesheets, scripts, images, fonts, and audio.
- `preload/{language}/{episode}.json`: original intro data.
- `docker/nginx.conf.template`: Nginx configuration, adapted from the [library's README](https://github.com/Richie314/Php-Simple-MVC#nginx).

The Nginx configuration serves assets directly, including missing-file responses, compression, MIME types, and caching. Other application requests are rewritten to the front controller and sent to PHP-FPM over the local Unix socket. Application source, dependencies, configuration, and preload data cannot be served directly. The library's inherited static `RequireLogin` helper is blocked from HTTP access.

### Installation under a URL prefix

Set `SITE_PATH_PREFIX` without a trailing slash. The container supplies the same value to Nginx and PHP-FPM:

```sh
SITE_PATH_PREFIX=/demo docker compose up --build -d starwars
# Open http://localhost:8080/demo/
```

Nginx serves `/demo/assets/` itself, and the router registers controller paths under `/demo`. For a host installation, use PHP >= 8.3 with mysqli, Composer, Nginx, and PHP-FPM. Install dependencies with `composer install`, supply `MYSQL_HOST`, `MYSQL_USER`, `MYSQL_PASSWORD`, and `MYSQL_DATABASE_NAME` to FPM, import `db/init.sql`, and adapt the Nginx template's root and FastCGI upstream to the installation.

## User model and database

The installed Simple MVC library defines `Richie314\SimpleMvc\Users\User` with `Id: int`, `Name: string`, and `Admin: bool`. It does not define SQL tables or a user repository. The application's `StarWars\Models\User` extends that class directly and inherits all three identity fields; it adds persistence, email, and a private password hash.

- `users.Id` is an auto-incrementing integer primary key; `users.Name` is the unique username.
- `users.PasswordHash` holds bcrypt hashes in a `VARCHAR(255)` column. The library's `Security::Hash()` and `TestPassword()` implement password handling.
- `users.Admin` is a boolean with a database constraint restricting it to 0 or 1.
- `login.UserId` and `openings.AuthorId` reference `users.Id`, independently of usernames. Intro attribution resolves the current username through a join.
- Login records have their own integer `Id` and microsecond timestamps; rapid logins remain separate records.
- Deleting a user cascades their login records and sets their intros' `AuthorId` to null, preserving the intros and their share URLs.

`User::Load($db, $id)` loads by integer identity; `User::FindByName($db, $name)` is used for username-based authentication. Admin deletion accepts an integer `id`, not a username. Sessions store only the integer ID. Each authenticated request reloads the actual user from the database and passes it and the mysqli connection to the library router with `SetUser()` and `SetDbConnection()`. Deleted accounts and role revocations therefore take effect on the next request.

### Initialize or reset the database

The application initializes a fresh database from `db/init.sql`. Previous database versions are not migrated. To discard stored accounts and intros and recreate the current schema:

```sh
docker compose down --volumes
docker compose up --build -d starwars
```

This deletes the database volume. The next startup recreates it, imports the current schema, and starts with no user data.

## Random generation

`StarWars\Support\Random` replaces the old global helpers:

```php
Random::int(1, 9);        // An integer in [1, 9].
Random::float();          // A value in [0, 1].
Random::float(-10, 10);   // Finite, ordered bounds.
Random::percentage();    // CSS percentage with a locale-independent decimal point.
Random::password();      // 12 random characters by default.
Random::password(24);    // Explicit positive length.
```

All generation uses `random_int()` rather than `lcg_value()`. Reversed or non-finite float bounds and non-positive password lengths are rejected. The class is loaded through Composer's normal PSR-4 mapping; no global helper files are included during autoloading.

## Original intro format

Each intro JSON contains `title`, `episode`, and a `content` array with one string per original line. Empty strings preserve blank lines; a final empty string preserves a trailing newline. The loader joins the lines with LF (`\n`) to reconstruct the text and paragraph breaks. Leading UTF-8 BOMs are intentionally removed. Do not trim lines or remove empty entries.

## Verification

```sh
composer validate --strict
composer dump-autoload --optimize --strict-psr
find app -name '*.php' -print0 | xargs -0 -n1 php -l
php tests/preload.php
php tests/random.php
php tests/bootstrap.php
node tests/urls.cjs
docker compose exec starwars nginx -t
python3 tests/smoke.py http://localhost:8080
```

The preload check verifies all 18 intros against the original text hashes, line endings, and metadata, allowing only the intended BOM removals. The JavaScript checks exercise preview, sharing, deletion, and playback initialization at root and under a prefix.

Run database checks only against an isolated test database; they create test accounts and intros:

```sh
python3 tests/smoke.py http://localhost:8080 --database
```

In an isolated stack, fresh-schema and user-contract checks can also create a temporary database and remove it afterward:

```sh
docker compose exec \
  -e RUN_DATABASE_TESTS=1 -e TEST_MYSQL_ROOT_PASSWORD=starwars \
  starwars php tests/database.php
```

These checks require the test server's root password. They verify the current schema, numeric usernames, foreign keys, password changes, identity inheritance, session invalidation, and live role revocation.

The HTTP suite checks controller routes, layouts, static-file responses, cookies, authentication, intro CRUD, and integer-ID ownership restrictions. Email delivery requires a configured mail transport and is not covered.

## Continuous integration

The GitHub Actions `CI` workflow runs on pushes, pull requests, and manual dispatch. It checks JavaScript, builds the combined Docker image, validates PHP and server configuration, and runs preload, random-generation, session-error, fresh-schema, and user-management tests. HTTP checks cover authentication, admin actions, intro CRUD, and both root and `/demo` URLs.

CI loads the image locally for testing and does not publish it to a registry. Failed runs print container diagnostics, and the test containers and database volume are always cleaned up.

## Credits

Vector artwork was created by Riccardo Ciucci or provided by [SVG Repo](https://www.svgrepo.com). The Star Wars logo and music belong to Disney. This project's code may not be used for profit.

Originally developed for the Web Programming examination in the Computer Engineering degree at the [University of Pisa](https://www.unipi.it), taught by [Alessio Vecchio](https://vecchio.iet.unipi.it/vecchio/), academic year 2023–2024.
