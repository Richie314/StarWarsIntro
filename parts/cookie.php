<?php if (!isset($_COOKIE["cookie-policy"]) || $_COOKIE["cookie-policy"] !== "accepted") { ?>
    <dialog id="accept-cookie">
        <h1>
            Questo sito necessita dei cookie
        </h1>
        <p>
            Questo sito impiega cookie per mantenere il login degli utenti.
            Non vi sono cookie a scopo di tracciamento o cookie di terze parti
        </p>
        <button type="button" id="accept-cookie-btn">
            Accetto
        </button>
    </dialog>
    <script type="text/javascript">
        (() => {
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
        })();
    </script>
<?php } ?>