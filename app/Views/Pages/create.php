<?php
use StarWars\Models\OpeningLanguage;
?>
<h1 class="aurebesh" data-content="<?= htmlspecialchars($title) ?>"> </h1>
<form class="intro-form" method="post" action="<?= htmlspecialchars($p . '/opening/create') ?>">
    <input type="hidden" name="id" value="<?= $opening->Id ?>" required>

    <div class="grid" style="grid-template-columns: 30% 70%">

        <label for="episode" class="center">
            Episodio:
        </label>
        <input type="text" id="episode" name="episode" required
            placeholder="Episodio X"
            value="<?= htmlspecialchars($opening->Episode) ?>"
            spellcheck="true">

        <label for="title" class="center">
            Titolo:
        </label>
        <input type="text" id="title" name="title" required
            placeholder="Un nuovo titolo"
            value="<?= htmlspecialchars($opening->Title) ?>"
            spellcheck="true">

        <label for="lang" class="center">
            Lingua:
        </label>
        <select id="lang" name="lang" required>
            <option value="<?= OpeningLanguage::Italian->value ?>"
                <?= $opening->Language === OpeningLanguage::Italian ? "selected" : "" ?>>
                Italiano
            </option>
            <option value="<?= OpeningLanguage::English->value ?>"
            <?= $opening->Language === OpeningLanguage::English ? "selected" : "" ?>>
                Inglese
            </option>
        </select>

        <div class="span-2">
            <br>
            <label for="content" class="center" style="font-size: larger">
                Testo qui:
            </label>
            <br>
            <textarea maxlength="4096"
                name="content" id="content"
                cols="30" rows="17"
                placeholder="Vai a capo due volte per dividere in paragrafi"
                spellcheck="true" ><?php
                    if (trim($opening->Content ?? '') !== '')
                        echo htmlentities($opening->Content);
                ?></textarea>
            <br>
            <?php
            if ($error !== null) {
                echo "<span>" . htmlspecialchars($error) . "</span>";
            }
            ?>
        </div>
        <div class="span-2">
            <h2>
                Ecco un'anteprima della intro che stai creando:
            </h2>
        </div>
        <div class="tv span-2">
            <iframe src="<?= htmlspecialchars($p . '/opening/view') ?>" data-view-url="<?= htmlspecialchars($p . '/opening/view') ?>" allowfullscreen id="tv"
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
                title="<?= $opening->Id !== 0 ? "Modifica" : "Crea" ?>"></button>
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
