<?php 
    require_once "./utils/random.php";
    if (!isset($MAX_STARS) || !is_int($MAX_STARS))
    {
        $MAX_STARS = 100;
    }
    for ($i = 0; $i < $MAX_STARS; $i++) { 
        if (random_float() > 0.94) {
?>
    <div class="star star-big" style="top: <?= random_percentage() ?>; left: <?= random_percentage() ?>"></div>
<?php 
        } else {
?>
    <div class="star" style="top: <?= random_percentage() ?>; left: <?= random_percentage() ?>"></div>
<?php
        }
    }
?>