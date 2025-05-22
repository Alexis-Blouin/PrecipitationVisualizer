<?php

declare(strict_types=1);

require __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR .  'vendor\autoload.php';
require __DIR__ . DIRECTORY_SEPARATOR  . '..\config\config.php';

use PhpMqtt\Client\Exceptions\MqttClientException;
use PhpMqtt\Client\MqttClient;
// La partie avec 'SimpleLogger' et 'LogLevel' est commentée, mais peut servir à du débogage plus tard.
//use PhpMqtt\Client\Examples\Shared\SimpleLogger;
//use Psr\Log\LogLevel;

// Chaine de connexion pour se connecter à la base de données avec PDO.
$chaineConnexion = "mysql:host=" . HOTE . ";dbname=" . BASE_DE_DONNEES;
$connexionPDO = null;

//$afficheur = new SimpleLogger(LogLevel::INFO);

try {
    // Crée la connexion avec la base de données.
    $connexionPDO = new PDO($chaineConnexion, UTILISATEUR, MOT_DE_PASSE);
    if ($connexionPDO) {
        //$afficheur->info("Connexion à la base de données " . BASE_DE_DONNEES . " réussie!");
    }

    // Définir le fuseau horaire par défaut à utiliser. Dans ce cas, le fuseau horaire de Montréal est utilisé.
    date_default_timezone_set('America/Montreal');
} catch (PDOException $e) {
    // Si une erreur survient lors de la connexion, le programme termine.
    //$afficheur->error("Erreur PDO : " . $e->getMessage());
    //die();
}

try {
    // Pour la connexion MQTT, j'ai modifié le fichier "..\vendor\php-mqtt\client\src\MqttClient.php" aux lignes 763 à 819.
    // Crée le client MQTT.
    $client = new MqttClient(MQTT_BROKER_HOST, MQTT_BROKER_PORT, 'test-subscriber', MqttClient::MQTT_3_1);

    // Lance la connexion avec le courtier. Le deuxième paramètre permet d'activer le mode sécurisé.
    $client->connect(null, true, $connexionPDO);

    // Souscription aux topics avec une qualité de service de niveau 0.
    // Le paramètre null est envoyé à la place d'une fonction de rappel, car dans ma situation je n'en ai pas besoin.
    if ($_GET["topic"] === "1") {
        $client->subscribe(TOPIC_TEMPERATURE, null, MqttClient::QOS_AT_MOST_ONCE);
    } else {
        $client->subscribe(TOPIC_DISTANCE, null, MqttClient::QOS_AT_MOST_ONCE);
    }

    // Boucle une fois pour recevoir le message.
    $client->loop(true);

    // Termine la connexion avec le courtier.
    $client->disconnect();
} catch (MqttClientException $e) {
    // Exception de base pour MQTT.
    //$afficheur->error("La souscription à un des topics a échoué : $e");
} catch (PDOException $e) {
    // Exception de base pour PDO.
    //$afficheur->error("L'envoi des données à la base de données a échoué : $e");
}
