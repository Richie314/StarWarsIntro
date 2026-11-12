"""HTTP regressions. --database must only target an isolated test database."""
import argparse
import http.cookiejar
from html.parser import HTMLParser
import json
import re
import urllib.error
import urllib.parse
import urllib.request
import uuid

parser = argparse.ArgumentParser()
parser.add_argument('url', nargs='?', default='http://localhost:8000')
parser.add_argument('--database', action='store_true')
parser.add_argument('--admin-username')
parser.add_argument('--admin-password')
args = parser.parse_args()
base = args.url.rstrip('/')
prefix = urllib.parse.urlsplit(base).path

class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None

class HTML(HTMLParser):
    def __init__(self, body):
        super().__init__()
        self.elements = []
        self.feed(body)

    def handle_starttag(self, tag, attrs):
        self.elements.append((tag, dict(attrs)))

    def count(self, tag):
        return sum(name == tag for name, _ in self.elements)

    def attributes(self, tag):
        return [attrs for name, attrs in self.elements if name == tag]

jar = http.cookiejar.CookieJar()
client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar), NoRedirect())

def request(path, data=None, status=200):
    encoded = None if data is None else urllib.parse.urlencode(data).encode()
    try:
        response = client.open(base + path, encoded, timeout=15)
    except urllib.error.HTTPError as error:
        response = error
    body = response.read().decode()
    assert response.code == status, (path, response.code, body[:700])
    assert not re.search(r'(?:Warning:|Fatal error:|Deprecated:|Notice:)', body), body[:700]
    return body, response.headers

def page(path, playback=False, status=200):
    body, headers = request(path, status=status)
    html = HTML(body)
    assert html.count('html') == html.count('head') == html.count('body') == html.count('main') == 1, path
    assert html.count('nav') == html.count('footer') == (0 if playback else 1), path
    scripts = [script.get('src', '') for script in html.attributes('script')]
    jquery = 'https://code.jquery.com/jquery-4.0.0.min.js'
    assert scripts.count(jquery) == (0 if playback else 1), path
    if not playback:
        assert scripts.index(jquery) < scripts.index(prefix + '/assets/page.js'), path
    if playback:
        assert 'accept-cookie' not in body and '/assets/page.js' not in body, path
        assert any(script.get('data-audio-url') == prefix + '/assets/intro.mp3' for script in html.attributes('script')), path
    assert body.lower().count('<!doctype html>') == 1, path
    routes = {'/', '/auth', '/auth/register', '/auth/forgotPassword', '/auth/changePassword', '/auth/logout',
              '/opening/create', '/opening/view', '/opening', '/admin', '/opening/delete', '/admin/deleteUser', '/acceptCookies', '/language', '/credits', '/error'}
    for tag, attrs in html.elements:
        for attr in ('href', 'src', 'action', 'data-view-url', 'data-delete-url'):
            target = attrs.get(attr, '')
            if not target or target.startswith(('#', 'javascript:', 'mailto:', 'https://', 'http://')):
                continue
            parsed = urllib.parse.urlsplit(target)
            assert parsed.path.startswith(prefix + '/'), (path, attr, target)
            relative = parsed.path[len(prefix):]
            assert relative in routes or relative.startswith('/assets/'), (path, attr, target)
            if relative.startswith('/assets/'):
                asset, asset_headers = request(relative)
                if relative.endswith('.css'):
                    assert asset_headers['Content-Type'].startswith('text/css'), target
                if relative.endswith('.js'):
                    assert 'javascript' in asset_headers['Content-Type'], target
    return body, html

def redirect(path, data=None, target=None, status=303):
    _, headers = request(path, data, status)
    location = headers['Location']
    assert location.startswith(prefix + '/'), location
    if target is not None:
        assert location == prefix + target, (path, location, target)
    return location[len(prefix):]

for asset in ['/assets/page.css', '/assets/does-not-exist.css']:
    _, headers = request(asset, status=404 if 'does-not-exist' in asset else 200)
    assert 'Set-Cookie' not in headers and 'X-Powered-By' not in headers, asset
for path in ['/RequireLogin', '/auth/RequireLogin', '/opening/RequireLogin', '/admin/RequireLogin',
             '/app/Models/User.php', '/vendor/autoload.php', '/preload/en/1.json', '/.env']:
    _, headers = request(path, status=404)
    assert 'X-Powered-By' not in headers, path

for path in ['/', '/?campaign=example', '/index', '/auth', '/auth/index', '/auth/register', '/auth/forgotPassword']:
    page(path)
assert 'name="password"' not in page('/auth/forgotPassword')[0]
assert 'name="email"' in page('/auth/register')[0]
assert '<title>Log in</title>' in page('/auth')[0]
# Interface language is independent of an intro's content language.
assert '<html lang="en">' in page('/')[0]
request('/language', status=400)
request('/language', {'locale': 'fr'}, status=400)
# Type mismatches and unknown named arguments use the library router's error response.
request('/language', {'locale[]': 'it'}, status=500)
for locale, heading, login, reset in [('it', 'Creatore Intro di Star Wars', 'Accedi', 'Reimposta password'),
                                       ('en', 'Star Wars Intro Creator', 'Log in', 'Reset password')]:
    redirect('/language', {'locale': locale, 'return_to': prefix + '/auth?from=language'}, target='/auth?from=language')
    home, html = page('/')
    assert f'<html lang="{locale}">' in home and heading in home
    assert f'<title>{login}</title>' in page('/auth')[0]
    assert f'<title>{reset}</title>' in page('/auth/forgotPassword')[0]
    page('/auth/register')
    credits, credits_html = page('/credits')
    assert f'<html lang="{locale}">' in credits
    assert 'Educational and Personal Noncommercial License 1.0' in credits
    assert 'Neale Davidson' in credits and 'Twemoji' in credits and 'John Williams' in credits
    assert credits_html.count('details') == 3
    assert ('Crediti e licenza' if locale == 'it' else 'Credits and license') in credits
    page('/missing', status=404)
    assert any(button.get('value') == locale and button.get('aria-pressed') == 'true' for button in html.attributes('button'))
    assert any(cookie.name == 'site-language' and cookie.value == locale and cookie.path == (prefix or '/') and cookie.expires for cookie in jar)
    assert f'<html lang="{locale}">' in page('/opening/view?original=1', playback=True)[0]
    assert '<html lang="it">' in page('/opening/view?original=1&lang=it', playback=True)[0]
for target in ['https://example.invalid/', '//example.invalid/', prefix + '/auth\r\nInjected: bad', prefix + '/auth\\bad']:
    redirect('/language', {'locale': 'en', 'return_to': target}, target='/')
if prefix:
    redirect('/language', {'locale': 'en', 'return_to': '/auth'}, target='/')
for lang in ['en', 'it']:
    for episode in range(1, 10):
        page(f'/opening/view?original={episode}&lang={lang}', playback=True)
redirect('/view.php?original=1&lang=en', target='/opening/view?original=1&lang=en', status=308)
assert '&lt;script&gt;' in page('/opening/view?title=%3Cscript%3E&content=Test', playback=True)[0]
assert '&amp;amp;' not in page('/opening/view?title=A%26B', playback=True)[0]
page('/missing/deep/path', status=404)
page('/opening/view?original=99', status=404)
page('/opening/view?original=0', status=404)
page('/opening/view?id=0', status=400)
page('/opening/view?lang[]=it', status=500)
page('/error?code=499&err=%3Cscript%3E', status=400)
for path in ['/opening', '/opening/index', '/admin', '/opening/delete', '/admin/deleteUser', '/opening/create']:
    redirect(path, target='/auth', status=302)
# Check a fresh protected visit without stale cookies from the unrelated guard checks above.
for cookie in list(jar):
    if cookie.name == 'Redirect':
        jar.clear(cookie.domain, cookie.path, cookie.name)
redirect('/opening/create', target='/auth', status=302)
# The library restores the destination when its cookie scope covers the auth URL.
assert any(cookie.name == 'Redirect' and urllib.parse.unquote(cookie.value).endswith('/opening/create') for cookie in jar)
redirect('/acceptCookies', {'return_to': prefix + '/auth'}, target='/auth')
assert 'id="accept-cookie"' not in page('/auth')[0]
assert any(cookie.name == 'cookie-policy' and cookie.path == (prefix or '/') for cookie in jar)
redirect('/acceptCookies', {'return_to': 'https://example.invalid/'}, target='/')
request('/acceptCookies', status=400)

if args.database:
    username = 'mvc' + uuid.uuid4().hex[:12]
    password = 'TestPassword123'
    redirect('/auth/register', dict(username=username, password=password, email=username + '@example.test'), target='/opening/create' if prefix else '/opening')
    dashboard, dashboard_html = page('/opening')
    assert username in dashboard
    assert not any('data-intro-card' in attrs for _, attrs in dashboard_html.elements)
    assert 'hidden' not in next(attrs for _, attrs in dashboard_html.elements if attrs.get('id') == 'intro-empty')
    page('/admin', status=401)
    page('/opening/create?id[]=1', status=500)
    _, html = page('/auth/changePassword')
    fields = {field.get('name') for field in html.attributes('input')}
    assert 'new_password' in fields and 'username' not in fields
    location = redirect('/opening/create', dict(id='0', title='MVC Test & Friends', episode='Episode X', lang='en',
                                       content='First paragraph\r\n\r\nSecond paragraph'))
    opening_id = urllib.parse.parse_qs(urllib.parse.urlsplit(location).query)['id'][0]
    assert location == f'/opening/create?id={opening_id}&action=share'
    page(location)
    for locale in ['it', 'en']:
        redirect('/language', {'locale': locale, 'return_to': prefix + '/opening/create?id=' + opening_id},
                 target='/opening/create?id=' + opening_id)
        editor, editor_html = page('/opening/create?id=' + opening_id)
        assert f'<html lang="{locale}">' in editor and 'MVC Test &amp; Friends' in editor
        assert '<html lang="en">' in page('/opening/view?id=' + opening_id, playback=True)[0]
        selected = next(option for option in editor_html.attributes('option') if 'selected' in option)
        assert selected['value'] == 'en', 'Interface changes must preserve saved intro language'
        new_editor, new_html = page('/opening/create')
        assert next(option for option in new_html.attributes('option') if 'selected' in option)['value'] == locale
        page('/opening')
        page('/auth/changePassword')
    dashboard, dashboard_html = page('/opening')
    assert sum('data-intro-card' in attrs for _, attrs in dashboard_html.elements) == 1
    assert 'MVC Test &amp; Friends' in dashboard
    assert 'hidden' in next(attrs for _, attrs in dashboard_html.elements if attrs.get('id') == 'intro-empty')
    rendered_intro = page('/opening/view?id=' + opening_id, playback=True)[0]
    assert prefix + '/credits' in rendered_intro
    assert 'MVC Test &amp; Friends' in rendered_intro and username in rendered_intro
    redirect('/opening/create', dict(id=opening_id, title='Edited Test', episode='Episode X', lang='it', content='Updated'),
             target=f'/opening/create?id={opening_id}&action=edited')
    assert 'Edited Test' in page('/opening/view?id=' + opening_id, playback=True)[0]
    redirect('/auth/changePassword', dict(password=password, new_password='ChangedPassword123'), target='/opening')
    assert json.loads(request('/opening/delete', dict(id=opening_id))[0])['esit'] == 'ok'
    page('/opening/view?id=' + opening_id, status=404)
    request('/opening/delete', status=400)
    request('/opening/delete', {'id': 'invalid'}, status=500)
    redirect('/auth/logout', target='/')
    redirect('/auth', dict(username=username, password='ChangedPassword123'), target='/opening')
    location = redirect('/opening/create', dict(id='0', title='Private owner', episode='Episode X', lang='en', content='Ownership'))
    owned_id = urllib.parse.parse_qs(urllib.parse.urlsplit(location).query)['id'][0]
    redirect('/auth/logout', target='/')
    redirect('/auth/register', dict(username=username + 'b', password=password, email=username + 'b@example.test'), target='/opening')
    assert 'Private owner' not in page('/opening')[0]
    page('/opening/create?id=' + owned_id, status=401)
    request('/opening/create', dict(id=owned_id, title='Stolen', episode='X', lang='en'), status=401)
    assert json.loads(request('/opening/delete', dict(id=owned_id))[0])['esit'] == 'error'
    assert 'Private owner' in page('/opening/view?id=' + owned_id, playback=True)[0]
    request('/admin/deleteUser', dict(username=username), status=401)
    if args.admin_username:
        redirect('/auth/logout', target='/')
        redirect('/auth', dict(username=args.admin_username, password=args.admin_password), target='/opening')
        for locale, heading in [('it', 'Accessi recenti'), ('en', 'Recent logins')]:
            redirect('/language', {'locale': locale, 'return_to': prefix + '/admin'}, target='/admin')
            admin_body, admin_page = page('/admin')
            assert f'<html lang="{locale}">' in admin_body and heading in admin_body
        page('/admin/index')
        assert json.loads(request('/opening/delete', dict(id=owned_id))[0])['esit'] == 'ok'
        inactive = [attrs for _, attrs in admin_page.elements
                    if attrs.get('data-delete-url') == prefix + '/admin/deleteUser']
        assert inactive, 'Seed an inactive test user before running the admin checks.'
        for account in inactive:
            assert account['data-delete-field'] == 'id' and int(account['data-delete-id']) > 0
            assert json.loads(request('/admin/deleteUser', dict(id=account['data-delete-id']))[0])['esit'] == 'ok'
        request('/admin/deleteUser', dict(username=username), status=500)
print('All layout, controller route, Nginx asset, cookie, and playback checks passed'
      + ('; database/authentication/ownership checks passed' if args.database else '') + f' ({base})')
