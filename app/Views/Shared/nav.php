<?php
$links = [['/', 'home', 'Home']];
if ($user !== null) {
    $links[] = ['/opening/create', 'create', 'Create'];
    $links[] = ['/opening', 'user', 'My intros'];
    if ($user->Admin) {
        $links[] = ['/admin', 'admin', 'Admin'];
    }
    $links[] = ['/auth/logout', 'logout', 'Log out'];
} else {
    $links[] = ['/auth/register', 'signup', 'Sign up'];
    $links[] = ['/auth', 'login', 'Log in'];
}
$currentPath = substr(parse_url($requestPath, PHP_URL_PATH), strlen($p));
?>
<nav class="site-nav" aria-label="<?= $t('Main navigation') ?>">
    <div class="nav-inner">
        <a class="nav-brand" href="<?= htmlspecialchars($p . '/') ?>">
            <img src="<?= htmlspecialchars($p) ?>/assets/sitelogo.svg" width="38" height="38" alt="">
            <span>STAR WARS <strong>INTRO</strong></span>
        </a>
        <button type="button" class="nav-toggle" id="nav-toggle" aria-expanded="false" aria-controls="nav-links"
            aria-label="<?= $t('Open menu') ?>" data-open-label="<?= $t('Open menu') ?>" data-close-label="<?= $t('Close menu') ?>">
            <img data-menu-open src="<?= htmlspecialchars($p) ?>/assets/img/hamburger.svg" width="24" height="24" alt="">
            <img data-menu-close src="<?= htmlspecialchars($p) ?>/assets/img/close.svg" width="24" height="24" alt="">
        </button>
        <div class="nav-links" id="nav-links">
            <?php foreach ($links as [$path, $icon, $label]) { ?>
                <a href="<?= htmlspecialchars($p . $path) ?>" <?= $currentPath === $path || $currentPath === rtrim($path, '/') . '/index' ? 'aria-current="page"' : '' ?>
                    <?php if ($path === '/opening') { ?>title="<?= htmlspecialchars($user->Name) ?>"<?php } ?>>
                    <img src="<?= htmlspecialchars($p . '/assets/img/' . $icon . '.svg') ?>" width="19" height="19" alt="">
                    <span><?= $t($label) ?></span>
                </a>
            <?php } ?>
        </div>
        <form class="language-switch" method="post" action="<?= htmlspecialchars($p . '/language') ?>" aria-label="<?= $t('Choose language') ?>">
            <input type="hidden" name="return_to" value="<?= htmlspecialchars($requestPath) ?>">
            <?php foreach (['en' => ['united-kingdom', 'English'], 'it' => ['italy', 'Italiano']] as $code => [$flag, $name]) { ?>
                <button type="submit" name="locale" value="<?= $code ?>" lang="<?= $code ?>"
                    aria-label="<?= $name ?>" title="<?= $name ?>" aria-pressed="<?= $locale === $code ? 'true' : 'false' ?>">
                    <img src="<?= htmlspecialchars($p . '/assets/img/' . $flag . '.svg') ?>" width="22" height="16" alt="">
                    <span><?= strtoupper($code) ?></span>
                </button>
            <?php } ?>
        </form>
    </div>
</nav>
