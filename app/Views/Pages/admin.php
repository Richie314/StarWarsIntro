<h1>
    <?= $t("Recent logins") ?>
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
            <?= $t('on') ?> <?= $login->When->format($locale === 'it' ? 'd/m/Y H:i:s' : 'Y-m-d H:i:s') ?>
        </li>
    <?php } ?>
</ul>
<h1>
    <?= $t("Inactive users") ?>
</h1>
<ul id="inactive-list">
    <?php foreach ($inactive as $inactiveUser) { ?>
        <li id="user-<?= $inactiveUser->Id ?>">
            <?= htmlspecialchars($inactiveUser->Name) ?>
            <?php if ($inactiveUser->Email !== '') { ?>
                &rarr;
                <a href="mailto:<?= htmlspecialchars($inactiveUser->Email) ?>"
                    class="link" title="<?= $t("Send an email") ?>">
                    <?= htmlspecialchars($inactiveUser->Email) ?>
                </a>
            <?php } ?>
            &rarr;
            <a href="#" data-delete-url="<?= htmlspecialchars($p . '/admin/deleteUser') ?>" data-delete-id="<?= $inactiveUser->Id ?>" data-delete-field="id" data-delete-row="user-<?= $inactiveUser->Id ?>" class="link">
                <?= $t("Delete") ?> <i class="icon delete"></i>
            </a>
        </li>
    <?php } ?>
</ul>
