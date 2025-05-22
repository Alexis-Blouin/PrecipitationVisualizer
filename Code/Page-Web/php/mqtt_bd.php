<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . "entete.php";
?>


<?php if ($connexionOK) : ?>
    <div id="chargement" class="alert alert-warning">
        Récupération des données en cours...
    </div>

    <script src="..\scripts\mqtt_bd.js" defer></script>

    <body onload="debuterIntervalle('<?= $uniteTempSelectionnee ?>', '<?= $uniteLongueurSelectionnee ?>')" />
<?php else : ?>
    <!-- Si la connexion n'a pas fonctionnée, on va chercher le HTML qui affiche l'erreur. -->
    <?php
    $messageErreur = ERREUR_CONN_BD . $pdo->getMessageErreur();
    ?>

    <body onload="afficherAlerte('<?= $messageErreur ?>')" />
<?php endif; ?>
</div>

</body>