const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const { JSDOM } = require('jsdom');
function classes(initial = []) {
    const set = new Set(initial);
    return { add: name => set.add(name), remove: name => set.delete(name), contains: name => set.has(name),
        toggle(name, enabled) { enabled ? set.add(name) : set.delete(name); } };
}


async function checkPages() {
    for (const prefix of ['', '/demo']) {
        const dom = new JSDOM(`<body data-view-url="${prefix}/opening/view"><nav></nav>
            <p id="action-feedback" hidden></p><dialog id="accept-cookie"></dialog>
            <form class="intro-form"><input id="episode" name="episode" value="Episode X">
            <input id="title" name="title" value="Test &amp; Friends">
            <input id="lang" name="lang" value="en"><textarea id="content" name="content">First paragraph

Second paragraph</textarea></form>
            <iframe id="tv" data-view-url="${prefix}/opening/view"></iframe><button id="reload-tv"></button>
            <a id="show-originals" href="#show-originals-dialog">Originals</a>
            <dialog id="show-originals-dialog"><select id="dialog-select"><option value="0">Choose</option><option value="4-it">IV</option></select></dialog>
            <dialog id="share-dialog"><button id="share-btn"></button><button data-close-dialog></button></dialog>
            <span data-intro-count>1</span><section id="intro-empty" hidden></section>
            <article id="row" data-intro-card><button data-delete-url="${prefix}/opening/delete" data-delete-field="id" data-delete-id="42" data-delete-row="row">Delete</button></article>
            </body>`, { url: `http://example.test:8099${prefix}/opening/create?id=42&action=share`, runScripts: 'outside-only' });
        const w = dom.window;
        w.HTMLDialogElement.prototype.showModal = function () { this.open = true; };
        w.HTMLDialogElement.prototype.close = function () { this.open = false; };
        let shared;
        w.navigator.canShare = () => true;
        w.navigator.share = async data => { shared = data; };
        w.eval(fs.readFileSync(require.resolve('jquery'), 'utf8'));
        for (const script of ['page', 'create', 'manage']) w.eval(fs.readFileSync(`assets/${script}.js`, 'utf8'));
        await new Promise(resolve => w.setTimeout(resolve, 30));
        const $ = w.jQuery;
        $.fx.off = true;
        assert.ok($('#accept-cookie')[0].open);
        const preview = new URL($('#tv').attr('src'), w.location.origin);
        assert.equal(preview.pathname, prefix + '/opening/view');
        assert.equal(preview.searchParams.get('title'), 'Test & Friends');
        assert.equal(preview.searchParams.get('content').replaceAll('\r', ''), 'First paragraph\n\nSecond paragraph');
        $('#show-originals').trigger('click');
        assert.ok($('#show-originals-dialog')[0].open);
        $('#dialog-select').val('4-it').trigger('change');
        assert.equal(new URL($('#tv').attr('src'), w.location.origin).searchParams.get('original'), '4');
        assert.equal($('#tv').attr('lang'), 'it');
        $('#share-btn').trigger('click');
        await Promise.resolve();
        assert.equal(shared.url, `http://example.test:8099${prefix}/opening/view?id=42`);
        assert.equal(w.location.search, '?id=42');
        for (const outcome of ['error', 'network', 'ok']) {
            $.post = (url, data) => {
                assert.equal(url, prefix + '/opening/delete');
                assert.equal(data.id, 42);
                const request = $.Deferred();
                return outcome === 'network' ? request.reject() : request.resolve({ esit: outcome });
            };
            $('[data-delete-url]').trigger('click');
            assert.equal($('[data-intro-card]').length, outcome === 'ok' ? 0 : 1);
            if (outcome !== 'ok') {
                assert.equal($('#action-feedback').prop('hidden'), false);
                assert.equal($('[data-delete-url]').prop('disabled'), false);
            }
        }
        assert.equal($('[data-intro-count]').text(), '0');
        assert.equal($('#intro-empty').prop('hidden'), false);
        dom.window.close();
    }
    console.log('jQuery preview, dialogs, sharing and deletion checks passed at root and /demo.');
}
checkPages().catch(error => { console.error(error); process.exitCode = 1; });

for (const prefix of ['', '/demo']) {
    let audioUrl;
    const events = {};
    const audioEvents = {};
    const document = { body: null, readyState: 'loading',
        currentScript: { dataset: { audioUrl: prefix + '/assets/intro.mp3' } },
        getElementById: () => null, addEventListener: (name, callback) => { events[name] = callback; } };
    const playback = vm.createContext({ document, console, performance: { now: () => 0 },
        Audio: class { constructor(url) { audioUrl = url; this.paused = true; }
            addEventListener(name, callback) { audioEvents[name] = callback; } },
        setTimeout: () => 1, clearTimeout() {} });
    // Playback is intentionally loaded synchronously in <head>, before document.body exists.
    vm.runInContext(fs.readFileSync('assets/view/view.js', 'utf8'), playback);
    assert.equal(audioUrl, prefix + '/assets/intro.mp3');
    audioEvents.loadeddata();
    vm.runInContext('PageStyleLoaded(); PageAnimationsLoaded();', playback);
    document.readyState = 'interactive';
    document.body = { classList: classes(['wait']) };
    events.DOMContentLoaded();
    assert.equal(document.body.classList.contains('wait'), false);
}
console.log('Playback resolves audio before the body exists and waits for DOM readiness before starting.');
