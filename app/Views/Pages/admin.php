<h1>
    Login recenti
</h1>
<ul id="login-list">
    <?php foreach ($logins as $login) { ?>
        <li>
            <?php if ($login->Email !== null && $login->Email !== '') { ?>
                <a href="mailto:<?= htmlspecialchars($login->Email) ?>" class="link">
                    <?= htmlspecialchars($login->User->Name) ?>
                </a>
            <?php } else { ?>
                <?= htmlspecialchars($login->User->Name) ?>
            <?php } ?>
            @
            <a href="http://<?= htmlspecialchars($login->Ip) ?>" class="link" target="_blank">
                <?= htmlspecialchars($login->Ip) ?>
            </a>
            &nbsp;
            il <?= $login->When->format('d/m/Y H:i:s') ?>
        </li>
    <?php } ?>
</ul>
<h1>
    Utenti inattivi
</h1>
<ul id="inactive-list">
    <?php foreach ($inactive as $inactiveUser) { ?>
        <li id="user-<?= $inactiveUser->Id ?>">
            <?= htmlspecialchars($inactiveUser->Name) ?>
            <?php if ($inactiveUser->Email !== '') { ?>
                &rarr;
                <a href="mailto:<?= htmlspecialchars($inactiveUser->Email) ?>"
                    class="link" title="Invia un'email">
                    <?= htmlspecialchars($inactiveUser->Email) ?>
                </a>
            <?php } ?>
            &rarr;
            <a href="#" data-delete-url="<?= htmlspecialchars($p . '/admin/deleteUser') ?>" data-delete-id="<?= $inactiveUser->Id ?>" data-delete-field="id" data-delete-row="user-<?= $inactiveUser->Id ?>" class="link">
                Cancella <i class="icon delete"></i>
            </a>
        </li>
    <?php } ?>
</ul>
