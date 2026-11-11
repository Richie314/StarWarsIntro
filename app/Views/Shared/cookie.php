<?php
use Richie314\SimpleMvc\Utils\Cookie;
?>
<?php if (Cookie::Get('cookie-policy') !== 'accepted') { ?>
    <dialog id="accept-cookie">
        <h1>Questo sito necessita dei cookie</h1>
        <p>Questo sito impiega cookie per mantenere il login degli utenti.
            Non vi sono cookie a scopo di tracciamento o cookie di terze parti.</p>
        <form method="post" action="<?= htmlspecialchars($p . '/acceptCookies') ?>">
            <input type="hidden" name="return_to" value="<?= htmlspecialchars($requestPath) ?>">
            <button type="submit" id="accept-cookie-btn">Accetto</button>
        </form>
    </dialog>
<?php } ?>
