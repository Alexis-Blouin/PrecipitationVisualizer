<?php

declare(strict_types=1);

define('ERREUR_CONN_BD', 'Erreur de connexion à la base de données : ');

require_once __DIR__ . DIRECTORY_SEPARATOR . "classes\connexionPDO.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['uniteTemp'])) {
        $_SESSION["uniteTemp"] = $_POST["uniteTemp"];
    }
    if (isset($_POST['uniteLongueur'])) {
        $_SESSION["uniteLongueur"] = $_POST["uniteLongueur"];
    }
}

if (isset($_SESSION["uniteTemp"])) {
    $uniteTempSelectionnee = $_SESSION["uniteTemp"];
} else {
    $uniteTempSelectionnee = "C";
}

$uniteLongueurSelectionnee = "mm/cm";
if (isset($_SESSION["uniteLongueur"])) {
    $uniteLongueurSelectionnee = $_SESSION["uniteLongueur"];
}

// Instanciation de la classe qui permet de se connecter à la base de données.
$pdo = connexionPDO::getConn($uniteTempSelectionnee, $uniteLongueurSelectionnee);
$connexionOK = true;
// S'assure qu'il n'y a pas eu d'erreur lors de la connexion à la base de données.
if ($pdo->erreurConnexion()) {
    $connexionOK = false;
}

function ajouterElementBarreNavigation(string $fichier, string $nom): void
{
    $active = "";
    if (str_contains($_SERVER['SCRIPT_NAME'], $fichier)) {
        $active = "active";
    }

    echo '<li class="navigation ' . $active . '">
		<a class="lien-nav" href="' . $fichier . '">' . $nom . '</a>
		</li>';
}

// Fonction qui retourne l'icone de la météo selon la température et les précipitations.
function iconeMeteo($uniteTempSelectionnee, $uniteLongueurSelectionnee): string
{
    $pdo = connexionPDO::getConn($uniteTempSelectionnee, $uniteLongueurSelectionnee);

    // S'assure qu'il n'y a pas eu d'erreur lors de la connexion à la base de données.
    if (!$pdo->erreurConnexion()) {
        $temperatures = $pdo->donneeParJour(1);
        $precipitations = $pdo->donneeParJour(2);

        // Trouve la moyenne de la température de la journée.
        $moyenneTemp = array_sum($temperatures[0]) / count($temperatures[0]);
        // Trouuve les précipitations totale de la journée.
        $precipitations = $precipitations[0][count($precipitations[0]) - 1] - $precipitations[0][0];

        // Récupère les heures du levé et du couché du Soleil (en paramètre, c'est la latitude et la longitude de Sept-Îles).
        $infosSoleil = date_sun_info(time(), 50.13, -66.23);
        // Détermine si c'est le jour ou la nuit.
        $astre = (time() <= $infosSoleil["sunrise"] || time() >= $infosSoleil["sunset"]) ? "lune" : "soleil";

        // Selon la combinaison de température et de précipitations, retourne l'icone de la météo.
        if ($moyenneTemp >= 0 && $precipitations > 0) {
            return "pluie_$astre";
        } elseif ($moyenneTemp <= 0 && $precipitations > 0) {
            return "neige_$astre";
        } else {
            return $astre;
        }
    } else {
        return "erreur_connexion";
    }
}

?>

<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <!-- Va chercher les police de google. -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Oxygen">
    <link rel="stylesheet" type="text/css" href="../CSS/main.css" />
    <!-- Selon la page, on peut ajouter un fichier CSS additionnel. -->
    <?php if (basename($_SERVER['SCRIPT_NAME']) === 'graphique.php') : ?>
        <link rel="stylesheet" type="text/css" href="../CSS/graphique.css" />
    <?php endif; ?>
    <?php if (basename($_SERVER['SCRIPT_NAME']) === 'tableau.php') : ?>
        <link rel="stylesheet" type="text/css" href="../CSS/tableau.css" />
    <?php endif; ?>

    <!-- les constantes et les fonctions n'ont pas de 'defer' car elle n'ont pas de code qui est exécuté au chargement du fichier. -->
    <script src="..\scripts\constantes.js"></script>
    <script src="..\scripts\fonctions.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
    <script src="..\scripts\main.js" defer></script>
</head>

<body>

    <nav>
        <!-- <a href="https://www.flaticon.com/free-icons/sun" title="sun icons">Sun icons created by Freepik - Flaticon</a> -->
        <div><img src="../icones/<?= iconeMeteo($uniteTempSelectionnee, $uniteLongueurSelectionnee) ?>.png" alt="Erreur lors du chargement de l'image!"></div>
        <h1>Vos précipitations</h1>

        <div class="formNav">
            <form id="formUniteTemp" class="" method="post" action="accueil.php">
                <input type="radio" id="celcuis" name="uniteTemp" value="C" <?php if (!isset($_SESSION["uniteTemp"]) || $_SESSION["uniteTemp"] === "C") : ?> checked <?php endif; ?>>
                <label for=" celcuis">&deg;C</label>
                <input type="radio" id="fahreneit" name="uniteTemp" value="F" <?php if (isset($_SESSION["uniteTemp"]) && $_SESSION["uniteTemp"] === "F") : ?> checked <?php endif; ?>>
                <label for="fahreneit">&deg;F</label>
            </form>
            <form id="formuniteLongueur" class="" method="post" action="accueil.php">
                <input type="radio" id="metre" name="uniteLongueur" value="mm/cm" <?php if (!isset($_SESSION["uniteLongueur"]) || $_SESSION["uniteLongueur"] === "mm/cm") : ?> checked <?php endif; ?>>
                <label for=" metre">mm/cm</label>
                <input type="radio" id="pouce" name="uniteLongueur" value="po" <?php if (isset($_SESSION["uniteLongueur"]) && $_SESSION["uniteLongueur"] === "po") : ?> checked <?php endif; ?>>
                <label for="pouce">po</label>
            </form>
        </div>
    </nav>

    <div id="contenu" class="container">
        <aside class="collapse barre-nav">
            <button class="hamburger-menu">
                <img src="../icones/menu_burger.png" alt="Menu">
            </button>
            <ul>
                <?php ajouterElementBarreNavigation("accueil.php", "Accueil"); ?>
                <?php ajouterElementBarreNavigation("graphique.php", "Graphiques"); ?>
                <?php ajouterElementBarreNavigation("tableau.php", "Tableaux"); ?>
                <?php ajouterElementBarreNavigation("mqtt_bd.php", "Données en direct"); ?>
            </ul>
        </aside>