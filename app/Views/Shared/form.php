<form class="form" method="post" action="<?= htmlspecialchars($p . $formAction) ?>">
    <h2 class="aurebesh" data-content="<?= htmlspecialchars($title) ?>"></h2>
    <?php
    $fields = [];
    if (!($hideUsername ?? false)) {
        $fields[] = ['username', $t('Username'), 'text', 'user', 'username', 'username'];
    }
    if ($showEmail ?? false) {
        $fields[] = ['email', $t('Email'), 'email', 'email', 'example@mail.com', 'email'];
    }
    if (!($hidePassword ?? false)) {
        $fields[] = ['password', $t('Password'), 'password', 'lock', '············', $formAction === '/auth/register' ? 'new-password' : 'current-password'];
    }
    if ($showNewPassword ?? false) {
        $fields[] = ['new_password', $t('New password'), 'password', 'lock', '············', 'new-password'];
    }
    ?>
    <?php foreach ($fields as [$name, $label, $type, $icon, $placeholder, $autocomplete]) { ?>
        <div class="field">
            <label for="<?= $name ?>"><?= $label ?></label>
            <div>
                <i class="<?= $icon ?>"></i>
                <input type="<?= $type ?>" name="<?= $name ?>" id="<?= $name ?>"
                    placeholder="<?= $placeholder ?>" autocomplete="<?= $autocomplete ?>" required
                    <?php if ($type === 'password') { ?> minlength="8" maxlength="24"<?php } ?>
                    <?php if ($name === 'username') { ?> pattern="[A-Za-z0-9]+" maxlength="32"<?php } ?>>
            </div>
        </div>
    <?php } ?>
    <?php if ($error ?? null) { ?>
        <p style="max-width: 500px;"><?= nl2br(htmlspecialchars($error)) ?></p>
    <?php } ?>
    <?php if ($showForgotPassword ?? false) { ?>
        <p><?= $t("Forgot your password?") ?> <a href="<?= htmlspecialchars($p . '/auth/forgotPassword') ?>" class="link"><?= $t("Reset it here") ?></a></p>
    <?php } ?>
    <button type="submit"><?= htmlspecialchars($buttonLabel ?? $title) ?></button>
</form>
