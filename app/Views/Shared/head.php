<?php
$description ??= \StarWars\Support\I18n::text('Watch a custom Star Wars opening crawl.');
if ($status_code >= 400) {
    $title = \StarWars\Support\I18n::text('Error {code}', ['code' => $status_code]);
}
$playback ??= false;
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0<?= $playback ? ', maximum-scale=1.0, user-scalable=no' : '' ?>">
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="title" content="<?= htmlspecialchars($title) ?>">
    <meta name="description" content="<?= htmlspecialchars($description) ?>">
    <meta name="author" content="<?= htmlspecialchars($playback ? ($opening->AuthorName ?? 'Star Wars Intro') : 'Riccardo Ciucci') ?>">
    <meta name="robots" content="index,follow">
    <meta name="google" content="notranslate">
    <link rel="icon" href="<?= htmlspecialchars($p) ?>/assets/sitelogo.svg">
    <?php foreach (['og', 'twitter'] as $network) { ?>
        <meta property="<?= $network ?>:url" content="<?= htmlspecialchars($requestUrl) ?>">
        <meta property="<?= $network ?>:title" content="<?= htmlspecialchars($title) ?>">
        <meta property="<?= $network ?>:description" content="<?= htmlspecialchars($description) ?>">
        <meta property="<?= $network ?>:image" content="<?= htmlspecialchars($p) ?>/assets/sitelogo.svg">
    <?php } ?>
    <meta property="og:type" content="website">
    <meta property="twitter:card" content="summary_large_image">
    <?php if ($playback) { ?>
        <script src="<?= htmlspecialchars($p) ?>/assets/view/view.js" data-audio-url="<?= htmlspecialchars($p) ?>/assets/intro.mp3"></script>
        <link rel="stylesheet" href="<?= htmlspecialchars($p) ?>/assets/view/view.css" onload="PageStyleLoaded()">
        <link rel="stylesheet" href="<?= htmlspecialchars($p) ?>/assets/view/animations.css" onload="PageAnimationsLoaded()">
    <?php } else { ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($p) ?>/assets/page.css">
        <link rel="stylesheet" href="<?= htmlspecialchars($p) ?>/assets/font.css">
    <?php } ?>
</head>
