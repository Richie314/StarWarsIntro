<header class="dashboard-header">
    <div>
        <p class="dashboard-eyebrow"><?= $t("YOUR GALAXY") ?></p>
        <h1><?= $t("My intros") ?></h1>
        <p><span data-intro-count><?= count($intros) ?></span> <?= $t("intros in your collection") ?></p>
    </div>
    <a class="dashboard-primary" href="<?= htmlspecialchars($p . '/opening/create') ?>"><?= $t("+ Create an intro") ?></a>
</header>
<div class="dashboard-settings">
    <a class="link" href="<?= htmlspecialchars($p . '/auth/changePassword') ?>"><?= $t("Change password") ?></a>
    <?php if ($user?->Admin) { ?>
        <a class="link" href="<?= htmlspecialchars($p . '/admin') ?>#login-list"><?= $t("User logins") ?></a>
        <a class="link" href="<?= htmlspecialchars($p . '/admin') ?>#inactive-list"><?= $t("Inactive users") ?></a>
    <?php } ?>
</div>
<section class="intro-grid" aria-label="<?= $t("My intros") ?>">
    <?php foreach ($intros as $intro) {
        $date = $intro->LastEdit ?? $intro->Creation;
        $viewUrl = $p . '/opening/view?id=' . $intro->Id;
    ?>
        <article class="intro-card" id="intro-row-<?= $intro->Id ?>" data-intro-card aria-labelledby="intro-title-<?= $intro->Id ?>">
            <a class="intro-poster" href="<?= htmlspecialchars($viewUrl) ?>" target="_blank" rel="noopener"
                aria-label="<?= $t('Watch {title} (new tab)', ['title' => $intro->Title]) ?>">
                <div class="intro-crawl" aria-hidden="true">
                    <span><?= htmlspecialchars($intro->Episode) ?></span>
                    <strong><?= htmlspecialchars($intro->Title) ?></strong>
                    <p><?= htmlspecialchars($intro->Content ?? '') ?></p>
                </div>
                <span class="intro-play" aria-hidden="true">▶</span>
            </a>
            <div class="intro-details">
                <p class="intro-meta"><span><i class="icon <?= $intro->Language->value ?>"></i> <?= $t($intro->Language->value === 'it' ? 'Italian' : 'English') ?></span>
                    <time datetime="<?= $date->format('c') ?>"><?= $date->format($locale === 'it' ? 'd/m/Y' : 'Y-m-d') ?></time></p>
                <h2 id="intro-title-<?= $intro->Id ?>"><?= htmlspecialchars($intro->Title) ?></h2>
                <p class="intro-episode"><?= htmlspecialchars($intro->Episode) ?></p>
                <div class="intro-actions">
                    <a href="<?= htmlspecialchars($viewUrl) ?>" target="_blank" rel="noopener"><?= $t("Watch") ?></a>
                    <a href="<?= htmlspecialchars($p . '/opening/create') ?>?id=<?= $intro->Id ?>"><?= $t("Edit") ?></a>
                    <button type="button" data-delete-url="<?= htmlspecialchars($p . '/opening/delete') ?>"
                        data-delete-id="<?= $intro->Id ?>" data-delete-field="id" data-delete-row="intro-row-<?= $intro->Id ?>"
                        aria-label="<?= $t('Delete {title}', ['title' => $intro->Title]) ?>"><?= $t("Delete") ?></button>
                </div>
            </div>
        </article>
    <?php } ?>
</section>
<section id="intro-empty" class="intro-empty" <?= count($intros) ? 'hidden' : '' ?>>
    <img src="<?= htmlspecialchars($p) ?>/assets/sitelogo.svg" alt="" width="90" height="90">
    <h2><?= $t("Your story starts here") ?></h2>
    <p><?= $t("No intros yet. Bring your first adventure to life.") ?></p>
    <a class="dashboard-primary" href="<?= htmlspecialchars($p . '/opening/create') ?>"><?= $t("Create your first intro") ?></a>
</section>
