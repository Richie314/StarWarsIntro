const dialog = document.getElementById('accept-cookie');
const btn = document.getElementById('accept-cookie-btn');
btn.onclick = () => {
    const d = new Date();
    const COOKIE_DURATION_DAYS = 90;
    d.setTime(d.getTime() + (COOKIE_DURATION_DAYS * 24 * 3600 * 1000));
    document.cookie = `cookie-policy=accepted; expires=${d.toUTCString()}; samesite=strict`;
    dialog.close();
}
dialog.showModal();