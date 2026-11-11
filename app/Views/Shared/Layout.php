<!DOCTYPE html>
<html lang="it">
<?php require __DIR__ . '/head.php'; ?>
<body data-view-url="<?= htmlspecialchars($p . '/opening/view') ?>">
    <?php require __DIR__ . '/nav.php'; ?>
    <main class="<?= htmlspecialchars($mainClass ?? 'body') ?>">
        <?php $RenderBody(); ?>
    </main>
    <?php require __DIR__ . '/cookie.php'; ?>
    <?php require __DIR__ . '/stars.php'; ?>
    <?php require __DIR__ . '/footer.php'; ?>
    <?php foreach ($scripts ?? [] as $script) { ?>
        <script src="<?= htmlspecialchars($p . '/assets/' . $script) ?>" defer></script>
    <?php } ?>
</body>
</html>
