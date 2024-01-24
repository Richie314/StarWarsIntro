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
    <script src="./assets/cookie.js" defer></script>
<?php } ?>