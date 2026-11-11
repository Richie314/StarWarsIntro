// Run the actual browser scripts with DOM stubs to check preview and sharing URLs.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function classes(initial = []) {
    const set = new Set(initial);
    return { add: name => set.add(name), remove: name => set.delete(name), contains: name => set.has(name),
        toggle(name, enabled) { enabled ? set.add(name) : set.delete(name); } };
}

for (const prefix of ['', '/demo']) {
    const origin = 'http://example.test:8099';
    let shared;
    const nodes = {};
    for (const id of ['tv', 'episode', 'title', 'lang', 'content', 'clear', 'submit', 'reload-tv',
        'show-originals-dialog', 'dialog-select', 'dialog-close', 'share-dialog', 'share-btn', 'share-close-btn']) {
        nodes[id] = { value: '', dataset: {}, classList: classes(['visible']), attributes: {},
            addEventListener() {}, blur() {}, showModal() {}, close() {},
            hasAttribute(name) { return name in this.attributes; },
            getAttribute(name) { return this.attributes[name]; },
            setAttribute(name, value) { this.attributes[name] = value; },
            removeAttribute(name) { delete this.attributes[name]; } };
    }
    nodes.tv.dataset.viewUrl = prefix + '/opening/view';
    nodes.episode.value = 'Episode X';
    nodes.title.value = 'Test & Friends';
    nodes.lang.value = 'en';
    nodes.content.value = 'First paragraph\n\nSecond paragraph';
    const body = { classList: classes(['can-share']), dataset: { viewUrl: prefix + '/opening/view' } };
    const location = new URL(origin + prefix + '/opening/create?id=42&action=share');
    const context = vm.createContext({ URL, URLSearchParams, console, location, window: { location },
        history: { replaceState() {} }, navigator: { share: data => { shared = data; } },
        document: { body, getElementById: id => nodes[id] },
        IntersectionObserver: class { observe() {} }, setTimeout: () => 1, clearTimeout() {}, alert() {} });
    vm.runInContext(fs.readFileSync('assets/create.js', 'utf8'), context);
    vm.runInContext('UpdateTvSrc()', context);
    let preview = new URL(nodes.tv.src, origin);
    assert.equal(preview.pathname, prefix + '/opening/view');
    assert.equal(preview.searchParams.get('title'), 'Test & Friends');
    assert.equal(preview.searchParams.get('content'), nodes.content.value);
    vm.runInContext("ShowOriginal('4', 'it')", context);
    preview = new URL(nodes.tv.src, origin);
    assert.equal(preview.searchParams.get('original'), '4');
    assert.equal(preview.searchParams.get('lang'), 'it');
    assert.equal(nodes.tv.lang, 'it');
    nodes['share-btn'].onclick();
    assert.equal(shared.url, origin + prefix + '/opening/view?id=42');

    for (const available of [true, false]) {
        let sample;
        const pageBody = { classList: classes(), dataset: { viewUrl: prefix + '/opening/view' } };
        const page = vm.createContext({ URL, console, location, setTimeout() {},
            document: { body: pageBody, querySelector: () => ({}), querySelectorAll: () => [] },
            navigator: available ? { share() {}, canShare(data) { sample = data.url; return true; } } : {} });
        vm.runInContext(fs.readFileSync('assets/page.js', 'utf8'), page);
        assert.equal(pageBody.classList.contains('can-share'), available);
        if (available) {
            const url = new URL(sample);
            assert.equal(url.origin, origin);
            assert.equal(url.pathname, prefix + '/opening/view');
            assert.ok(Number(url.searchParams.get('original')) >= 1 && Number(url.searchParams.get('original')) <= 9);
        }
    }
}
console.log('Preview, original-intro, sharing URLs and browser capability checks passed at root and /demo.');

async function checkDeletion() {
    for (const [endpoint, id] of [['opening/delete', '42'], ['admin/deleteUser', '123']]) {
        const field = 'id';
        for (const success of [true, false]) {
            let handler, sent, removed = false;
            const link = { dataset: { deleteUrl: '/demo/' + endpoint,
                deleteId: id, deleteField: field, deleteRow: 'row' },
                addEventListener(event, callback) { handler = callback; } };
            const row = { classList: classes(), remove() { removed = true; } };
            const context = vm.createContext({
                document: { querySelectorAll: () => [link], getElementById: () => row },
                async post(url, data) { sent = { url, data }; return { esit: success ? 'ok' : 'error' }; },
                setTimeout(callback) { callback(); }
            });
            vm.runInContext(fs.readFileSync('assets/manage.js', 'utf8'), context);
            await handler({ preventDefault() {} });
            assert.equal(sent.url, link.dataset.deleteUrl);
            assert.equal(sent.data[field], id);
            assert.equal(removed, success);
        }
    }
    console.log('Intro and user deletion handlers preserve prefixed URLs and respect failed deletions.');
}
checkDeletion().catch(error => { console.error(error); process.exitCode = 1; });

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
