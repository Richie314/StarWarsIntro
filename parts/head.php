<?php 
    if (isEmpty($TITLE)) 
        $TITLE = "Pagina generica";
    if (isEmpty($DESCRIPTION)) 
        $DESCRIPTION = "Crea le tue intro di Star Wars personalizzate";
    if (isset($_SERVER['HTTPS']) &&
        ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
        isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
        $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
        
            $protocol = 'https://';
    } else {
      $protocol = 'http://';
    }
    $URL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $TITLE ?>
    </title>
    <meta name="title" content="<?= $TITLE ?>">
    <meta name="description" content="<?= $DESCRIPTION ?>">
    <meta name="author" content="Riccardo Ciucci">
    <meta name="robots" content="index,follow">
    <link rel="icon" href="./assets/sitelogo.svg">
    <link rel="shortcut icon" href="./assets/sitelogo.svg">

    <!-- Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $URL ?>">
    <meta property="og:title" content="<?= $TITLE ?>">
    <meta property="og:description" content="<?= $DESCRIPTION ?>">
    <meta property="og:image" content="./assets/sitelogo.svg">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= $URL ?>">
    <meta property="twitter:title" content="<?= $TITLE ?>">
    <meta property="twitter:description" content="<?= $DESCRIPTION ?>">
    <meta property="twitter:image" content="./assets/sitelogo.svg">

    <link rel="stylesheet" type="text/css" href="./assets/page.css" media="all">
    <link rel="stylesheet" type="text/css" href="./assets/font.css" media="print" onload="this.media = 'all'">
    <script src="./assets/page.js" type="text/javascript" defer></script>
</head>