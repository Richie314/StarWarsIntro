<?php 
    if (isEmpty($TITLE)) 
        $TITLE = "Pagina generica";
    if (isEmpty($DESCRIPTION)) 
        $DESCRIPTION = "Crea le tue intro di Star Wars personalizzate";
    $URL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $TITLE ?>
    </title>
    <meta name="title" content="<?= $TITLE ?>">
    <meta name="description" content="<?= $DESCRIPTION ?>">
    <meta name="author"content="Riccardo Ciucci">
    <meta name="robots" content="index,follow">

    <!-- Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $URL ?>">
    <meta property="og:title" content="<?= $TITLE ?>">
    <meta property="og:description" content="<?= $DESCRIPTION ?>">
    <meta property="og:image" content="/assets/icon.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= $URL ?>">
    <meta property="twitter:title" content="<?= $TITLE ?>">
    <meta property="twitter:description" content="<?= $DESCRIPTION ?>">
    <meta property="twitter:image" content="/assets/icon.png">

    <link rel="stylesheet" type="text/css" href="/assets/page.css" media="print" onload="this.media = 'all'">
    <script src="/assets/page.js" type="text/javascript" defer></script>
</head>