<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . "..\classes\connexionPDO.php";

// Réception des données des unités de mesure et de la date.
$uniteTemperature = $_GET["uniteTemperature"];
$uniteLongueur = $_GET['uniteLongueur'];
$date = $_GET["date"];

// Récupération de la classe qui permet de se connecter à la base de données.
$pdo = connexionPDO::getConn($uniteTemperature, $uniteLongueur);

// Si une erreur survient lors de la connexion à la base de données, on retourne un message d'erreur.
if ($pdo->erreurConnexion()) {
    $messageErreur = $pdo->getMessageErreur();
    echo json_encode(["Erreur BD", $messageErreur]);
} else {
    // Exécution de la fonction pour obtenir les données.
    $tableTemperature = $pdo->donneesJournalieres(1, $date);
    $tablePrecipitation = $pdo->donneesJournalieres(2, $date);

    // Envoi des données au format JSON.
    echo json_encode([$tableTemperature, $tablePrecipitation]);
}
