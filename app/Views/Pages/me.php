<header class="dashboard-header">
    <div>
        <p class="dashboard-eyebrow">LA TUA GALASSIA</p>
        <h1>Le mie intro</h1>
        <p><span data-intro-count><?= count($intros) ?></span> intro nella tua collezione</p>
    </div>
    <a class="dashboard-primary" href="<?= htmlspecialchars($p . '/opening/create') ?>">+ Crea una intro</a>
</header>
<div class="dashboard-settings">
    <a class="link" href="<?= htmlspecialchars($p . '/auth/changePassword') ?>">Cambia password</a>
    <?php if ($user?->Admin) { ?>
        <a class="link" href="<?= htmlspecialchars($p . '/admin') ?>#login-list">Login degli utenti</a>
        <a class="link" href="<?= htmlspecialchars($p . '/admin') ?>#inactive-list">Utenti inattivi</a>
    <?php } ?>
</div>
<section class="intro-grid" aria-label="Le mie intro">
    <?php foreach ($intros as $intro) {
        $date = $intro->LastEdit ?? $intro->Creation;
        $viewUrl = $p . '/opening/view?id=' . $intro->Id;
    ?>
        <article class="intro-card" id="intro-row-<?= $intro->Id ?>" data-intro-card aria-labelledby="intro-title-<?= $intro->Id ?>">
            <a class="intro-poster" href="<?= htmlspecialchars($viewUrl) ?>" target="_blank" rel="noopener"
                aria-label="Guarda <?= htmlspecialchars($intro->Title) ?> (nuova scheda)">
                <div class="intro-crawl" aria-hidden="true">
                    <span><?= htmlspecialchars($intro->Episode) ?></span>
                    <strong><?= htmlspecialchars($intro->Title) ?></strong>
                    <p><?= htmlspecialchars($intro->Content ?? '') ?></p>
                </div>
                <span class="intro-play" aria-hidden="true">▶</span>
            </a>
            <div class="intro-details">
                <p class="intro-meta"><span><i class="icon <?= $intro->Language->value ?>"></i> <?= $intro->Language->value === 'it' ? 'Italiano' : 'Inglese' ?></span>
                    <time datetime="<?= $date->format('c') ?>"><?= $date->format('d/m/Y') ?></time></p>
                <h2 id="intro-title-<?= $intro->Id ?>"><?= htmlspecialchars($intro->Title) ?></h2>
                <p class="intro-episode"><?= htmlspecialchars($intro->Episode) ?></p>
                <div class="intro-actions">
                    <a href="<?= htmlspecialchars($viewUrl) ?>" target="_blank" rel="noopener">Guarda ↗</a>
                    <a href="<?= htmlspecialchars($p . '/opening/create') ?>?id=<?= $intro->Id ?>">Modifica</a>
                    <button type="button" data-delete-url="<?= htmlspecialchars($p . '/opening/delete') ?>"
                        data-delete-id="<?= $intro->Id ?>" data-delete-field="id" data-delete-row="intro-row-<?= $intro->Id ?>"
                        aria-label="Elimina <?= htmlspecialchars($intro->Title) ?>">Elimina</button>
                </div>
            </div>
        </article>
    <?php } ?>
</section>
<section id="intro-empty" class="intro-empty" <?= count($intros) ? 'hidden' : '' ?>>
    <img src="<?= htmlspecialchars($p) ?>/assets/sitelogo.svg" alt="" width="90" height="90">
    <h2>La tua storia inizia qui</h2>
    <p>Non hai ancora nessuna intro. Dai vita alla tua prima avventura.</p>
    <a class="dashboard-primary" href="<?= htmlspecialchars($p . '/opening/create') ?>">Crea la tua prima intro</a>
</section>
