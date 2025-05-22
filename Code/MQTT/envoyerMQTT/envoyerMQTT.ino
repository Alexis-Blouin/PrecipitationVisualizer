#include <ArduinoMqttClient.h>
#include <WiFiNINA.h>
#include "DHT.h"
#include "mdp_acces_reseau.h"

// Broche digitale qui recevra les données du capteur.
#define BROCHEDHT 2
#define TYPEDHT DHT22 // Définition du type de capteur DHT.

// Broche digitale
#define BROCHEDECLENCHEUSE 4
#define BROCHEECHO 5

// Initialize DHT sensor.
DHT dht(BROCHEDHT, TYPEDHT);

// Ajout des données du fichier mdp_arduino à ce fichier
char ssid[] = SECRET_SSID; 
char mdp[] = SECRET_MDP;

WiFiClient wifiClient; // Instanciation d'un wifi pour la connexion
MqttClient mqttClient(wifiClient);
// Paramètres du courtier pour la communication
const char courtier[] = "test.mosquitto.org";
int        port     = 1883;
const char topicTemperature[]  = "/topic_DHT22_alexis/temperature";
const char topicDistance[]  = "/topic_HC-SR04_alexis";

// Variables pour la fonction de délai.
unsigned long millisPrecedent = 0;
unsigned long millisActuel = 0;

float temperature;

long duree;
int distance;

void setup() {
  // Tous les Serial.print() sont commentés pour alléger le code de remise du projet, mais pourraient être réutilisés pour de futurs tests.
  // Serial.begin(9600);
  //while (!Serial);

  // Serial.print("Tentative de connexion au SSID : ");
  // Serial.println(ssid);
  // Tentatives infinies pour le réseau de l'école.
  while (WiFi.begin(ssid, mdp) != WL_CONNECTED) {
    // Serial.print(".");
    delay(5000);
  }

  // Serial.println("Vous êtes connecté au réseau!");
  // Serial.println();

  // Serial.print("Tentative de connexion au courtier : ");
  // Serial.println(courtier);

  // Connexion au courtier
  if (!mqttClient.connect(courtier, port)) {
    // Serial.print("Échec de la connexion au courtier MQTT! Code d'erreur = ");
    // Serial.println(mqttClient.connectError());

    while (1);
  }

  // Serial.println("Vous êtes connecté au courtier MQTT!");
  // Serial.println();

  dht.begin();

  pinMode(BROCHEDECLENCHEUSE, OUTPUT);
  pinMode(BROCHEECHO, INPUT);
}

void loop() {
  // Lecture des données.
  lireTemperature();
  lireDistance();

  // Délai de 5 secondes entre les affichages.
  tempo(5000);
}

void lireTemperature(){
  // Lecture de la température en °C.
  temperature = dht.readTemperature();

  // Si la données n'est pas un nombre.
  if (isnan(temperature)) {
    // Serial.println(F("Échec de la lecture du capteur!"));
    return;
  }

  // Affichage de la température.
  // Serial.print(F("Temperature:"));
  // Serial.print(temperature);

  // Envoi de la donnée récupérée au courtier.
  mqttClient.beginMessage(topicTemperature);
  mqttClient.print(temperature);
  mqttClient.endMessage();
}

void lireDistance(){
  // S'assure qu'aucune données n'est sur la broche déclencheuse.
  digitalWrite(BROCHEDECLENCHEUSE, LOW);
  delayMicroseconds(2);

  // Génère les ultrasons pour 10 microsecondes.
  digitalWrite(BROCHEDECLENCHEUSE, HIGH);
  delayMicroseconds(10);
  digitalWrite(BROCHEDECLENCHEUSE, LOW);

  // Récupère les temps nécessaire aux ultasons pour revenir.
  duree = pulseIn(BROCHEECHO, HIGH);
  // Calcul de la distance.
  float distance = round(duree * 0.34 / 2);

  // S'assure que la récupération de la donnée a fonctionnée.
  if(duree == 0){
    // Serial.println(F("Échec de la lecture du capteur!"));
    return;
  }

  // Affiche la distance.
  // Serial.print(F("Distance:"));
  // Serial.println(distance);
  
  // Envoi de la donnée récupérée au courtier.
  mqttClient.beginMessage(topicDistance);
  mqttClient.print(distance);
  mqttClient.endMessage();
}

void tempo(int deltaT){
  // Tant que la différence entre millisPrecedent et millisActuel ne dépasse pas l'intervalle
  while(millisActuel - millisPrecedent < deltaT){
    millisActuel = millis();

    if (!mqttClient.connected()) {
      reconnexionMQTT();
    }

    // Appel fréquent de poll() pour éviter la déconnexion par le courtier.
    mqttClient.poll();
  }
  millisPrecedent = millisActuel; // Met à jour millisPrecedent avec millisActuel.
}

// Reconnexion au courtier si la connexion a été perdue.
void reconnexionMQTT() {
  while (!mqttClient.connected()) {
    // Serial.println("Tentative de reconnexion...");
    if (mqttClient.connect(courtier)) {
      // Serial.println("Reconnexion réuissie!");
    } else {
      // Serial.print("Echec, rc=");
      // Serial.print(mqttClient.State());
      // Serial.println("Nouvelle tentative dans 5 secondes...");
      delay(5000);
    }
  }
}
