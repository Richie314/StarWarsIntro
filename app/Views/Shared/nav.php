<?php
$links = [['/', 'home', 'Home']];
if ($user !== null) {
    $links[] = ['/opening/create', 'create', 'Crea'];
    $links[] = ['/opening', 'user', $user->Name];
    if ($user->Admin) {
        $links[] = ['/admin', 'admin', 'Admin'];
    }
    $links[] = ['/auth/logout', 'logout', 'Logout'];
} else {
    $links[] = ['/auth/register', 'signup', 'Registrati'];
    $links[] = ['/auth', 'login', 'Login'];
}
?>
<nav class="h-menu items-<?= count($links) ?>">
    <div class="hamburger">
        <input type="checkbox" id="hide-show-menu">
        <label for="hide-show-menu">
            <img src="<?= htmlspecialchars($p) ?>/assets/img/hamburger.svg" alt="Hamburger" title="Mostra il menù" class="hamburger">
            <img src="<?= htmlspecialchars($p) ?>/assets/img/close.svg" alt="Chiudi" title="Chiudi il menù" class="close">
        </label>
    </div>
    <?php foreach ($links as [$path, $icon, $label]) { ?>
        <a href="<?= htmlspecialchars($p . $path) ?>" title="Vai a <?= htmlspecialchars($label) ?>">
            <div class="icon"><img src="<?= htmlspecialchars($p . '/assets/img/' . $icon . '.svg') ?>" alt="<?= htmlspecialchars($label) ?>"></div>
            <div class="text"><span><?= htmlspecialchars($label) ?></span></div>
        </a>
    <?php } ?>
</nav>
