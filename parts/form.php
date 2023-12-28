<?php
    if (isEmpty($FORM_BUTTON_LABEL))
    {
        die("Form misconfigured.");
    }
    $SHOW_EMAIL_FIELD = isset($SHOW_EMAIL_FIELD) && $SHOW_EMAIL_FIELD;
?>
<form class="form" method="post">
    <h2 class="aurebesh" data-content="<?= $FORM_BUTTON_LABEL ?>"></h2>
    <div class="field">
        <label for="email">
            Username
        </label>
        <div>
            <i class="user"></i>
            <input type="text" 
                name="username" placeholder="username" 
                pattern="[A-Za-z0-9]+" required
                maxlength="32">
        </div>
    </div>
    <?php if ($SHOW_EMAIL_FIELD) { ?>
        <div class="field">
            <label for="email">
                Email
            </label>
            <div>
                <i class="email"></i>
                <input type="email" 
                    name="email" placeholder="esempio@mail.com" 
                    pattern="[^@\s]+@[^@\s]+\.[^@\s]+" required>
            </div>
        </div>
    <?php } ?>
    <div class="field">
        <label for="password">
            Password
        </label>
        <div>
            <i class="lock"></i>
            <input type="password" 
                name="password" placeholder="············" required
                minlength="8"
                maxlength="24">
        </div>
    </div>
    <?php if (!isEmpty($error_msg)) { ?>
        <p style="max-width: 500px;">
            <?= str_replace("\n", "<br>", htmlspecialchars($error_msg)) ?>
        </p>
    <?php } ?>
    <button type="submit">
        <?= $FORM_BUTTON_LABEL ?>
    </button>
</form>