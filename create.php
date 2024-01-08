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
            throw new Exception("Risorsa non trovata", 404);
        }
        if ($opening->Author !== $USER_ID && !$IS_ADMIN)
        {
            throw new Exception("Non possiedi la risorsa", 401);
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
                throw new Exception("Risorsa non trovata", 404);
            }
            if ($opening->Author !== $USER_ID && !$IS_ADMIN)
            {
                throw new Exception("Non possiedi la risorsa", 401);
            }
            $opening->Title = $_POST["title"];
            $opening->Episode = $_POST["episode"];
            $opening->Content = isEmpty($_POST["content"]) || !is_string($_POST["content"]) ? null : $_POST["content"];
            $opening->Language = Opening::StringToLanguage($_POST["lang"]);
            if ($opening->Upload($db))
            {
                header("Location: ./create.php?id=$opening->ID&action=edited");
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
                header("Location: ./create.php?id=$opening->ID&action=share");
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
    <main class="body">
        <h1 class="aurebesh" data-content="Crea nuova intro"> </h1>
        <form class="intro-form" method="post">
            <input type="hidden" name="id" value="<?= $opening->ID ?>" required>

            <div class="grid" style="grid-template-columns: 30% 70%">

                <label for="episode" class="center">
                    Episodio:
                </label>
                <input type="text" id="episode" name="episode" required
                    placeholder="Episodio X" pattern="[A-Za-z0-9\s'\-À-ÿ!\$%\+]+"
                    value="<?= htmlspecialchars($opening->Episode) ?>"
                    spellcheck="true">

                <label for="title" class="center">
                    Titolo:
                </label>
                <input type="text" id="title" name="title" required
                    placeholder="Un nuovo titolo" pattern="[A-Za-z0-9\s'\-À-ÿ!\$%\+]+"
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
                    <textarea maxlength="2048"
                        name="content" id="content" 
                        cols="30" rows="17"
                        placeholder="Vai a capo due volte per dividere in paragrafi"
                        spellcheck="true" ><?php 
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
                <div class="span-2">
                    <h2>
                        Ecco un'anteprima della intro che stai creando:
                    </h2>
                </div>
                <div class="tv span-2">
                    <iframe src="./view.php" allowfullscreen id="tv"
                        width="1600" height="900" scrolling="no"
                        loading="lazy" sandbox="allow-scripts">
                    </iframe>
                    <button type="button" id="reload-tv" title="Riavvia" class="tv-control">

                    </button>
                </div>
                <div class="span-2">
                    <p>
                        Vuoi dei suggerimenti?
                        Guarda le <strong><a href="javascript:ShowOriginals()" class="link">Intro originali</a></strong>
                    </p>
                </div>

                <div class="span-2 grid" style="grid-template-columns: 1fr 1fr">
                    <button type="reset" title="Cancella" id="clear"></button>
                    <button type="submit" id="submit"
                        title="<?= $opening->ID !== 0 ? "Modifica" : "Crea" ?>"></button>
                </div>
            </div>
        </form>
        <dialog id="show-originals-dialog">
            <h3>
                Scegli l'intro da emulare e guarda nella tv
            </h3>
            <select id="dialog-select">
                <option value="0">Scegli</option>

                <optgroup label="In italiano">
                    <option value="1-it">La minaccia fantasma</option>
                    <option value="2-it">L'attacco dei cloni</option>
                    <option value="3-it">La vendetta dei Sith</option>
                    <option value="4-it">Una nuova Speranza</option>
                    <option value="5-it">L'Impero colspisce ancora</option>
                    <option value="6-it">Il ritorno dello Jedi</option>
                    <option value="7-it">Il risveglio della Forza</option>
                    <option value="8-it">Gli ultimi Jedi</option>
                    <option value="9-it">L'ascesa di Skywalker</option>
                </optgroup>

                <optgroup label="In inglese">
                    <option value="1-en">The Phantom Menace</option>
                    <option value="2-en">The Attack of the Clones</option>
                    <option value="3-en">Revenge of the Sith</option>
                    <option value="4-en">A new Hope</option>
                    <option value="5-en">The Empire strikes back</option>
                    <option value="6-en">The return of the Jedi</option>
                    <option value="7-en">The Force awakens</option>
                    <option value="8-en">The last Jedi</option>
                    <option value="9-en">The rise of Skywalker</option>
                </optgroup>
            </select>
            <br>
            <button role="button" type="button" id="dialog-close">
                Annulla
            </button>
        </dialog>
        <dialog id="share-dialog">
            <h3>
                Condividi l'intro appena creata!
            </h3>
            <button role="button" type="button" id="share-btn">
                Condividi
            </button>
            <button role="button" type="button" id="share-close-btn">
                Per ora no
            </button>
        </dialog>
    </main>
    <?php include "./parts/stars.php" ?>
    <?php include "./parts/footer.php"; ?>
    <script src="./assets/create.js" defer type="text/javascript"></script>
</body>
</html>