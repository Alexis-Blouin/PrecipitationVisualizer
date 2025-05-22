# PrecipitationVisualizer
This repo is a school project I did at school to get precipitation level with an Arduino ultrasound sensor and visualize it on a website after getting the data from an MQTT broker

# Projet Précipitation

## Description
Ce projet a pour but de permettre la mesure à distance des précipitations en visualisant les données sous plusieurs angles.
Le projet comporte une partie Arduino qui envoie ses données à un courtier MQTT.
Ces données sont ensuite stockées dans une base de données MariaDB.
Une page Web vient ensuite récupérer les données pour les afficher et les rendre facilement interprétables par l'usager.

## Utilisation du projet
Dans le wiki de ce répertoire, vous pourrez trouver les guides d'installation et d'utilisation du projet.
Ils sont aussi disponibles en format PDF dans le dossier principal du répertoire.

## Liste des sources externes
Pour la communication MQTT, j'ai utilisé la librairie php-mqtt qui est disponible sur un répertoire GitHub :
https://github.com/php-mqtt/client-examples.<br/>
Au niveau de la page web, j'ai utilisé la librairie Chart.js pour faire des graphiques plus plaisants visuellement :
https://www.chartjs.org/docs/latest/

## Technologies utilisée
Voici une liste des différentes technologies que j'ai utilisées pour accomplir ce projet :
* Arduino
* MQTT
* PHP
* HTML/CSS/JavaScript
* MariaDB

## Matériel utilisé
Voici les liens vers les différents composants de mon projet :<br/>
* Capteur de distance HC-SR04 -<br/>
&#9;https://www.sparkfun.com/products/15569<br/>
* Arduino MRK 1001 Wifi -<br/>
&#9;https://store-usa.arduino.cc/products/arduino-mkr-wifi-1010?queryID=undefined&selectedStore=us<br/>
* Capteur de température DHT22 -<br/>
&#9;https://www.adafruit.com/product/385<br/>

