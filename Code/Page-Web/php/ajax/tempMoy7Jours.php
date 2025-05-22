<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . "..\classes\connexionPDO.php";

// Réception des données des unités de mesure et de la date.
$uniteTemperature = $_GET["uniteTemperature"];
$uniteLongueur = $_GET['uniteLongueur'];
$intervalle = $_GET['intervalle'];
$date = $_GET["date"];

// Récupération de la classe qui permet de se connecter à la base de données.
$pdo = connexionPDO::getConn($uniteTemperature, $uniteLongueur);

// Si une erreur survient lors de la connexion à la base de données, on retourne un message d'erreur.
if ($pdo->erreurConnexion()) {
    $messageErreur = $pdo->getMessageErreur();
    echo json_encode(["Erreur BD", $messageErreur]);
} else {
    // Exécution des fonctions pour obtenir les données.
    $temperatureMoyenne = $pdo->donneeParJour(1, $date,  $intervalle);
    $precipitationParJour = $pdo->donneeParJour(2, $date, $intervalle);

    // Envoi des données au format JSON.
    echo json_encode([$temperatureMoyenne, $precipitationParJour]);
}
