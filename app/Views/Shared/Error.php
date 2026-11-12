<?php
// Framework errors use fixed English banners; application messages are already localized.
$banner = $main_banner ?? '';
if ($status_code >= 500) {
    $banner = $t('Internal server error.');
} elseif ($status_code === 404) {
    $banner = $t('Resource not found.');
} elseif ($banner === 'You are not authorized to view this resource') {
    $banner = $t('You are not authorized to view this resource.');
} elseif ($banner === 'The was a problem in your request' || $banner === '') {
    $banner = $t('There was a problem with your request.');
} else {
    $banner = htmlspecialchars($banner);
}
?>
<?php if ($status_code === 404) { ?>
    <h1><?= $t('This is not the page you are looking for!') ?></h1>
    <div class="img no-ctx" style="max-width: 350px">
        <img src="<?= htmlspecialchars($p) ?>/assets/img/benkenobi.svg" alt="Obi-Wan Kenobi">
    </div>
<?php } else { ?>
    <h1><?= $t('An error occurred.') ?></h1>
<?php } ?>
<p><?= $banner ?></p>
<a class="link" href="<?= htmlspecialchars($p . '/') ?>"><?= $t('Back to the home page') ?></a>
