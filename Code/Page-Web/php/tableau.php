<?php

session_start();

$dateSelectionnee;
// Si l'utilisateur vient de sélectionner une nouvelle date, on la stocke dans la session.
if (isset($_POST["date"])) {
    // La nouvelle date ne paut pas être placée dans le futur.
    if (strtotime($_POST["date"]) > strtotime(date("Y-m-d"))) {
        $dateFutur = true;
    } else {
        $_SESSION["dateTableau"] = date('Y-m-d', strtotime($_POST["date"]));
        $dateSelectionnee = $_SESSION["dateTableau"];
    }
}

if (isset($_SESSION["dateTableau"])) {
    // Si elle est déjà stockée, on la récupère.
    $dateSelectionnee = $_SESSION["dateTableau"];
} else {
    // Sinon, on prend la date du jour.
    $dateSelectionnee = date('Y-m-d');
}

// Si l'utilisateur vient de sélectionner un nouveau choix de tableau, on le stocke dans la session.
if (isset($_POST["donneeTableauChoisie"])) {
    $_SESSION["donneeTableauChoisie"] = $_POST["donneeTableauChoisie"];
    $donneTableau = $_SESSION["donneeTableauChoisie"];
} else if (isset($_SESSION["donneeTableauChoisie"])) {
    // Si elle est déjà stockée, on la récupère.
    $donneTableau = $_SESSION["donneeTableauChoisie"];
} else {
    $donneTableau = "les tableaux des températures et des précipitations";
}

require_once __DIR__ . DIRECTORY_SEPARATOR . "entete.php";
?>

<?php if ($connexionOK) : ?>

    <!-- Affichage de l'erreur si la date a été placée dans le futur. -->
    <?php if (isset($dateFutur) && $dateFutur === true) : ?>
        <script>
            afficherAlerte(ERREUR_DATE_FUTUR, false);
        </script>
    <?php endif; ?>

    <body onload="tableau('<?= $uniteTempSelectionnee ?>', '<?= $uniteLongueurSelectionnee ?>', '<?= $dateSelectionnee ?>', '<?= $donneTableau ?>')">

        <div class="divFormulaires">
            <!-- Formulaire pour sélectionner une date -->
            <form id="formDate" method="post" action="tableau.php" hidden>
                <div class="row">
                    <label for="date">Sélectionez une date :</label>
                    <input type="date" name="date" value="<?= $dateSelectionnee ?>" />
                </div>
                <div class="row"><button type="submit">Confirmer</button></div>
            </form>

            <!-- Formulaire pour choisir le nombre de graphiques à afficher. -->
            <form id="formDonneeTableau" method="post" action="tableau.php" hidden>
                <div class="row">
                    <input type="radio" id="double" name="donneeTableauChoisie" value="les tableaux des températures et des précipitations" <?php if ($donneTableau === "les tableaux des températures et des précipitations") : ?>checked<?php endif; ?>>
                    <label for="double">Température et Précipitation</label>
                </div>
                <div class="row">
                    <input type="radio" id="temp" name="donneeTableauChoisie" value="le tableau des températures" <?php if ($donneTableau === "le tableau des températures") : ?>checked<?php endif; ?>>
                    <label for="temp">Température</label>
                </div>
                <div class="row">
                    <input type="radio" id="precip" name="donneeTableauChoisie" value="le tableau des précipitations" <?php if ($donneTableau === "le tableau des précipitations") : ?>checked<?php endif; ?>>
                    <label for="precip">Précipitation</label>
                </div>
            </form>
        </div>

        <div class="divTableaux"></div>

        <script src="..\scripts\tableau.js" defer></script>
    <?php else : ?>
        <!-- Si la connexion n'a pas fonctionnée, on va chercher le HTML qui affiche l'erreur. -->
        <?php
        $messageErreur = ERREUR_CONN_BD . $pdo->getMessageErreur();
        ?>

        <body onload="afficherAlerte('<?= $messageErreur ?>')" />
    <?php endif; ?>
    </div>
    </body>

    </html>