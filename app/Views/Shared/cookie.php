<?php
use Richie314\SimpleMvc\Utils\Cookie;
?>
<?php if (Cookie::Get('cookie-policy') !== 'accepted') { ?>
    <dialog id="accept-cookie">
        <h1><?= $t('This site uses cookies') ?></h1>
        <p><?= $t('Cookies keep you signed in and remember your language. There are no tracking or third-party cookies.') ?></p>
        <form method="post" action="<?= htmlspecialchars($p . '/acceptCookies') ?>">
            <input type="hidden" name="return_to" value="<?= htmlspecialchars($requestPath) ?>">
            <button type="submit" id="accept-cookie-btn"><?= $t('Accept') ?></button>
        </form>
    </dialog>
<?php } ?>
