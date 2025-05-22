<?php

session_start();

// Si le nombre de graphique à afficher est changé, on le sauvegarde dans la session.
if (isset($_POST["nombreGraphique"])) {
    $_SESSION["nombreGraphique"] = $_POST["nombreGraphique"];
}

// Si le type de graphique à afficher est changé, on le sauvegarde dans la session.
if (isset($_POST["typeGraphique"])) {
    $_SESSION["typeGraphique"] = $_POST["typeGraphique"];
    $typeGraphique = $_SESSION["typeGraphique"];
} elseif (isset($_SESSION["typeGraphique"])) {
    $typeGraphique = $_SESSION["typeGraphique"];
} else {
    $typeGraphique = "lignes";
}

// Regarde si les nouvelle dates sont valides.
if (isset($_POST["dateDebut"])) {
    if (strtotime($_POST["dateDebut"]) > strtotime($_POST["dateFin"])) {
        $mauvaisesDates = true;
    } elseif (trouverIntervalle($_POST["dateDebut"], $_POST["dateFin"]) > 31) {
        $mauvaisIntervalle = true;
    } else if (strtotime($_POST["dateFin"]) > strtotime(date("Y-m-d"))) {
        $dateFutur = true;
    } else {
        $_SESSION["dateDebut"] = $_POST["dateDebut"];
        $dateDebut = $_SESSION["dateDebut"];

        $_SESSION["dateFin"] = $_POST["dateFin"];
        $dateFin = $_SESSION["dateFin"];
    }
}

// Sinon, on prend les dates de la session ou par défaut, on prend la dernière semaine.
if (isset($_SESSION["dateDebut"])) {
    $dateDebut = $_SESSION["dateDebut"];
} else {
    $dateDebut = strtotime(date("Y-m-d"));
    $dateDebut = date("Y-m-d", strtotime("-6 days", $dateDebut));
}
if (isset($_SESSION["dateFin"])) {
    $dateFin = $_SESSION["dateFin"];
} else {
    $dateFin = date("Y-m-d");
}

$intervalle = trouverIntervalle($dateDebut, $dateFin);

function trouverIntervalle($dateDebut, $dateFin)
{
    $dateDebutStr = strtotime($dateDebut);
    $dateFinStr = strtotime($dateFin);

    return floor(abs($dateDebutStr - $dateFinStr) / (60 * 60 * 24));
}

require_once("entete.php");
?>

<?php if ($connexionOK) : ?>
    <!-- Si les dates ne sont pas valides, on affiche une alerte. -->
    <?php if (isset($mauvaisesDates) && $mauvaisesDates === true) : ?>
        <script>
            afficherAlerte(ERREUR_ORDRE_DATES, false);
        </script>
    <?php endif; ?>
    <?php if (isset($mauvaisIntervalle) && $mauvaisIntervalle === true) : ?>
        <script>
            afficherAlerte(ERREUR_INTERVALLE_GRAND, false);
        </script>
    <?php endif; ?>
    <?php if (isset($dateFutur) && $dateFutur === true) : ?>
        <script>
            afficherAlerte(ERREUR_DATE_FUTUR, false);
        </script>
    <?php endif; ?>

    <!-- Selon le nombre de graphique à afficher, on appelle 'unGraphique' ou 'deuxGraphique'. -->
    <?php if (!isset($_SESSION["nombreGraphique"]) || $_SESSION["nombreGraphique"] === "2 graphiques") : ?>

        <body onload="deuxGraphique('<?= $uniteTempSelectionnee ?>', '<?= $uniteLongueurSelectionnee ?>', '<?= $typeGraphique ?>', '<?= $dateFin ?>', '<?= $intervalle ?>')">
        <?php else : ?>

            <body onload="unGraphique('<?= $uniteTempSelectionnee ?>', '<?= $uniteLongueurSelectionnee ?>', '<?= $typeGraphique ?>', '<?= $dateFin ?>',  '<?= $intervalle ?>')">
            <?php endif; ?>

            <div class="divFormulaires">
                <!-- Formulaire pour sélectionner une date -->
                <form id="periodeGraphique" method="post" action="graphique.php" hidden>
                    <div class="row">
                        <label for="dateDebut">Début de l'intervalle :</label>
                        <input type="date" name="dateDebut" value="<?= $dateDebut ?>" />
                    </div>
                    <div class="row">
                        <label for="dateFin">Fin de l'intervalle :</label>
                        <input type="date" name="dateFin" value="<?= $dateFin ?>" />
                    </div>
                    <div class="row"><button type="submit">Confirmer</button></div>
                </form>

                <div class="row">
                    <!-- Formulaire pour choisir le nombre de graphiques à afficher. -->
                    <form id="formNombreGraphique" method="post" action="graphique.php" hidden>
                        <input type="radio" id="deux" name="nombreGraphique" value="2 graphiques" <?php if (!isset($_SESSION["nombreGraphique"]) || $_SESSION["nombreGraphique"] === "2 graphiques") : ?>checked<?php endif; ?>>
                        <label for="un">2 graphiques</label>
                        <input type="radio" id="un" name="nombreGraphique" value="1 graphique" <?php if (isset($_SESSION["nombreGraphique"]) && $_SESSION["nombreGraphique"] === "1 graphique") : ?>checked<?php endif; ?>>
                        <label for="un">1 graphique</label>
                    </form>
                </div>
                <div class="row">
                    <!-- Formulaire pour choisir le type de graphique à afficher. -->
                    <form id="formTypeGraphique" method="post" action="graphique.php" hidden>
                        <input type="radio" id="ligne" name="typeGraphique" value="lignes" <?php if (!isset($_SESSION["typeGraphique"]) || $_SESSION["typeGraphique"] === "lignes") : ?>checked<?php endif; ?>>
                        <label for="temp">Lignes</label>
                        <input type="radio" id="bande" name="typeGraphique" value="bandes" <?php if (isset($_SESSION["typeGraphique"]) && $_SESSION["typeGraphique"] === "bandes") : ?>checked<?php endif; ?>>
                        <label for="longueur">Bandes</label>
                    </form>
                </div>
            </div>

            <div class="canvas"></div>



            <script src="..\scripts\graphique.js" defer></script>
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