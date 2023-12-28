<?php
    require_once "./utils/session.php";
    include_once "./utils/opening.php";
    
    $TITLE = "Crea nuova intro";
    $DESCRIPTION = "Crea nuova o modifica una intro.";
    
    $opening = new Opening(0, 'Titolo qui', 'Episodio X', null, 'it', $USER_ID, null, null);
    if (isset($_GET["id"]) && ctype_digit($_GET["id"]))
    {
        $opening = Opening::Load($db, (int)$_GET["id"]);
        if (!isset($opening))
        {
            http_response_code(404);
            throw new Exception("Risorsa non trovata");
        }
        if ($opening->Author !== $USER_ID && !$IS_ADMIN)
        {
            http_response_code(401);
            throw new Exception("Non possiedi la risorsa");
        }
    }

    if (
        !isEmpty($_POST["id"]) && ctype_digit($_POST["id"]) &&
        !isEmpty($_POST["episode"]) && is_string($_POST["episode"]) &&
        !isEmpty($_POST["title"]) && is_string($_POST["title"]) &&
        !isEmpty($_POST["lang"]) && is_string($_POST["lang"])
    ) {
        
        if ((int)$_POST["id"] !== 0)
        {
            // Update
            $opening = Opening::Load($db, (int)$_POST["id"]);
            if (!isset($opening))
            {
                http_response_code(404);
                throw new Exception("Risorsa non trovata");
            }
            if ($opening->Author !== $USER_ID && !$IS_ADMIN)
            {
                http_response_code(401);
                throw new Exception("Non possiedi la risorsa");
            }
            $opening->Title = $_POST["title"];
            $opening->Episode = $_POST["episode"];
            $opening->Content = isEmpty($_POST["content"]) || !is_string($_POST["content"]) ? null : $_POST["content"];
            $opening->Language = Opening::StringToLanguage($_POST["lang"]);
            if ($opening->Upload($db))
            {
                header("Location: ./view.php?id=$opening->ID");
                exit;
            }
            $error_msg = "Impossibile aggiornare la risorsa.";
        } else {
            $opening = new Opening(
                0,
                $_POST["title"],
                $_POST["episode"],
                isEmpty($_POST["content"]) || !is_string($_POST["content"]) ? null : $_POST["content"],
                $_POST["lang"],
                $USER_ID,
                null, null
            );
            if ($opening->Upload($db))
            {
                header("Location: ./view.php?id=$opening->ID");
                exit;
            }
            $error_msg = "Impossibile creare la risorsa.";
        }
    }
?>
<!DOCTYPE html>
<html lang="it">
<?php include "./parts/head.php"; ?>
<body>
    <?php include "./parts/nav.php"; ?>
    <div class="body">
        <h1 class="aurebesh" data-content="Crea nuova intro"> </h1>
        <form class="intro-form" method="post">
            <input type="hidden" name="id" value="<?= $opening->ID ?>" required>

            <div class="grid" style="grid-template-columns: 30% 70%">

                <label for="episode" class="center">
                    Episodio:
                </label>
                <input type="text" id="episode" name="episode" required
                    placeholder="Episodio X" pattern="[A-Za-z0-9\s]+"
                    value="<?= htmlspecialchars($opening->Episode) ?>"
                    spellcheck="true">

                <label for="title" class="center">
                    Titolo:
                </label>
                <input type="text" id="title" name="title" required
                    placeholder="Un nuovo titolo" pattern="[A-Za-z0-9\s]+"
                    value="<?= htmlspecialchars($opening->Title) ?>"
                    spellcheck="true">

                <label for="lang" class="center">
                    Lingua:
                </label>
                <select id="lang" name="lang" required>
                    <option value="<?= OpeningLanguage::Italian->value ?>" 
                        <?= $opening->Language === OpeningLanguage::Italian ? "selected" : 0 ?>>
                        Italiano
                    </option>
                    <option value="<?= OpeningLanguage::English->value ?>"
                    <?= $opening->Language === OpeningLanguage::English ? "selected" : 0 ?>>
                        Inglese
                    </option>
                </select>

                <div class="span-2">
                    <br>
                    <label for="content" class="center" style="font-size: larger">
                        Testo qui:
                    </label>
                    <br>
                    <textarea 
                        name="content" id="content" 
                        cols="30" rows="15"
                        placeholder="Vai a capo due volte per dividere in paragrafi"
                        spellcheck="true"><?php 
                            if (!isEmpty($opening->Content)) 
                                echo htmlentities($opening->Content);
                        ?></textarea>
                    <br>
                    <?php 
                    if (!isEmpty($error_msg)) {
                        echo "<span>$error_msg</span>";
                    }
                    ?>
                </div>

                <div class="span-2 grid" style="grid-template-columns: 1fr 1fr">
                    <button type="reset" title="Cancella"></button>
                    <button type="submit" 
                        title="<?= $opening->ID !== 0 ? "Modifica" : "Crea" ?>"></button>
                </div>
            </div>
        </form>
    </div>
    <?php include "./parts/stars.php" ?>
    <?php include "./parts/footer.php"; ?>
</body>
</html>