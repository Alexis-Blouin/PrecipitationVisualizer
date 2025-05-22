<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . "..\config\config.php";

class connexionPDO
{
    private static $conn;

    // Variable qui contient la connexion à la base de données.
    private PDO $connexionPDO;
    // Unités de mersure des données.
    public string $uniteTemperature;
    public string $uniteLongueur;

    // Variable qui servira à déterminer si les précipitation sont de la neige ou de la pluie.
    private float $derniereTemperature;

    // Variable qui contient l'erreur PDO, s'il y a lieu.
    private ?PDOException $erreurPDO = null;

    // Constraucteur de la classe qui crée une connexion avec la base de données.
    private function __construct($uniteTemperature, $uniteLongueur)
    {
        // Initialisation des varaibles locales.
        $this->uniteTemperature = $uniteTemperature;
        $this->uniteLongueur = $uniteLongueur;
        if ($uniteTemperature === "C") {
            $this->derniereTemperature = 0;
        } else {
            $this->derniereTemperature = 32;
        }

        // Chaine de connexion pour se connecter à la base de données avec PDO.
        $chaineConnexion = "mysql:host=" . HOTE . ";dbname=" . BASE_DE_DONNEES;

        try {
            // Tente de créer la connexion avec la base de données.
            $this->connexionPDO = new PDO($chaineConnexion, UTILISATEUR, MOT_DE_PASSE, [PDO::FETCH_ASSOC]);

            // Définir le fuseau horaire par défaut à utiliser. Dans ce cas, le fuseau horaire de Montréal est utilisé.
            date_default_timezone_set('America/Montreal');
        } catch (PDOException $e) {
            $this->erreurPDO = $e;
        }
    }

    // Fonction pour le singleton de la classe.
    public static function getConn($uniteTemperature, $uniteLongueur)
    {
        // Si la classe n'est pas déjà créée, on la crée une seule fois et ensuite on ne fait que la récupérer.
        if (!isset(self::$conn)) {
            self::$conn = new connexionPDO($uniteTemperature, $uniteLongueur);
        }
        return self::$conn;
    }

    // Fonction qui retourne si une erreur est survenue lors de la connexion à la base de données.
    public function erreurConnexion()
    {
        if (!is_null($this->erreurPDO)) {
            return true;
        } else {
            return false;
        }
    }

    // Fonction qui retourne le message d'erreur de la connexion à la base de données.
    public function getMessageErreur()
    {
        return $this->erreurPDO->getMessage();
    }

    // Fonction qui retourne la dernière donnée enregistrée dans la base de données.
    public function derniereDonnee(int $idParametre): array
    {
        $commandeSQL = "SELECT * FROM donnees WHERE ID_Parametre = :ID_Parametre ORDER BY Date DESC, Heure DESC LIMIT 1;";
        $expression = $this->connexionPDO->prepare($commandeSQL);
        $expression->bindParam(":ID_Parametre", $idParametre, PDO::PARAM_INT);

        $expression->execute();
        $resultat = $expression->fetchAll()[0];
        if ($idParametre === 1) {
            $this->derniereTemperature = ($this->uniteTemperature === "F") ? $this->celciusVersFahrenheit($resultat["Valeur"]) : $resultat["Valeur"];
        }

        if ($this->uniteLongueur !== "po") {
            if (($this->derniereTemperature >= 0 && $this->uniteTemperature === "C") || ($this->derniereTemperature >= 32 && $this->uniteTemperature === "F")) {
                $this->uniteLongueur = "mm";
            } else {
                $this->uniteLongueur = "cm";
            }
        }
        $donnees = ["Date" => $resultat["Date"], "Heure" => $resultat["Heure"], "Unite" => $this->uniteLongueur];

        if ($idParametre === 1) {
            $donnees["Valeur"] = $resultat["Valeur"];
        } else {
            // Récupère la date actuelle.
            $date = strtotime(date("Y-m-d"));
            $resultats = $this->precipitationsJour($date, 0);
            $donnees["Valeur"] = ($this->uniteLongueur === "mm" || $this->uniteLongueur === "po") ? $resultats["Valeur"] : $resultats["Valeur"] / 10;
        }

        // Une seule ligne sera récupérée, mais elle sera quand même mise dans un tableau, donc on prend l'indice 0.
        return $donnees;
    }

    // Permet de récupérer les précipitations totale des 7 dernier jours.
    public function precipitationsSemaine()
    {
        // Récupère la date d'aujourd'hui.
        $date = strtotime(date("Y-m-d"));

        $totalPrecipitation = 0;
        for ($i = 0; $i < 7; $i++) {
            // Pour les 7 jours, on récupère les précipitations reçues, puis on les ajoute au total selon l'unité de mesure.
            $resultat = $this->precipitationsJour($date, $i);
            $totalPrecipitation += ($this->uniteLongueur === "mm" || $this->uniteLongueur === "po") ? $resultat["Valeur"] : $resultat["Valeur"] / 10;
            $unite = $resultat["Unite"];
        }

        return ["Valeur" => $totalPrecipitation, "Unite" => $unite];
    }

    // Fonction qui retourne les prcipitations de la journée demandée.
    private function precipitationsJour(int $date, int $decalage)
    {
        // Tranformation de la date.
        $nouvelleDate = strtotime("-$decalage day", $date);
        $nouvelleDate = date("Y-m-d", $nouvelleDate);

        // Récupération des données.
        $commandeSQL = "SELECT * FROM donnees WHERE ID_Parametre = 2 AND Date = :Date;";
        $expression = $this->connexionPDO->prepare($commandeSQL);
        $expression->bindParam(":Date", $nouvelleDate, PDO::PARAM_STR);

        $expression->execute();

        $resultats = $expression->fetchAll();

        if (!empty($resultats)) {
            $debutJournee = $resultats[0]["Valeur"];
            $finJournee = $resultats[count($resultats) - 1]["Valeur"];

            $precipitation = $debutJournee - $finJournee;

            // Détermine l'unité selon la dernière température reçue.
            if ($this->uniteLongueur !== "po") {
                if (($this->derniereTemperature >= 0 && $this->uniteTemperature === "C") || ($this->derniereTemperature >= 32 && $this->uniteTemperature === "F")) {
                    $this->uniteLongueur = "mm";
                } else {
                    $this->uniteLongueur = "cm";
                }
            }

            // Si la données est négative, on ne fait que retourner 0.
            if ($precipitation > 0) {
                return ["Valeur" => $precipitation, "Unite" => $this->uniteLongueur];
            } else {
                return ["Valeur" => 0, "Unite" => $this->uniteLongueur];
            }
        } else {
            return ["Valeur" => 0, "Unite" => $this->uniteLongueur];
        }
    }

    // Retounre les données pour les graphiques.
    public function donneeParJour(int $idParametre, string $date = null, int $intervalle = 6)
    {
        $donneesJournalieres = [];

        if ($date === null) {
            $date = strtotime(date("Y-m-d"));
        }

        // Boucle pour toutes les journées de l'intevralle.
        $compteDonneesTotales = 0;
        for ($i = $intervalle; $i >= 0; $i--) {
            $nouvelleDate = strtotime("-$i day", strtotime($date));
            $nouvelleDate = date("Y-m-d", $nouvelleDate);
            $commandeSQL = "SELECT * FROM donnees WHERE ID_Parametre = :ID_Parametre AND Date = :Date;";
            $expression = $this->connexionPDO->prepare($commandeSQL);
            $expression->bindParam(":Date", $nouvelleDate, PDO::PARAM_STR);
            $expression->bindParam(":ID_Parametre", $idParametre, PDO::PARAM_STR);

            $expression->execute();

            $resultats = $expression->fetchAll(PDO::FETCH_ASSOC);

            // Si aucune données n'a été récupérée, on met la valeur 0.
            if (!empty($resultats)) {
                if ((int)$idParametre === 1) {
                    $temperatureTotale = 0;
                    $ii = 0;
                    foreach ($resultats as $resultat) {
                        $temp = $resultat["Valeur"];
                        if ($this->uniteTemperature === "F") {
                            $temp = $this->celciusVersFahrenheit($temp);
                        }
                        $temperatureTotale += $temp;
                        $ii++;
                    }
                    // On fait la moyenne des températures de la journée.
                    $donneesJournalieres[] = $temperatureTotale / $ii;
                    $compteDonneesTotales += $ii;
                } else {
                    $debutJournee = $resultats[0]["Valeur"];
                    $finJournee = $resultats[count($resultats) - 1]["Valeur"];

                    $precipitation = $debutJournee - $finJournee;
                    if ($precipitation > 0) {
                        if ($this->uniteLongueur === "po") {
                            $precipitation = $this->millimetreVersPouce($precipitation);
                        }
                        $donneesJournalieres[] = $precipitation;
                    } else {
                        $donneesJournalieres[] = 0;
                    }

                    $compteDonneesTotales += count($resultats);
                }
            } else {
                $donneesJournalieres[] = 0;
            }
        }

        return [$donneesJournalieres, $compteDonneesTotales];
    }

    // Récupère les données complètes pour une journée en particulier.
    public function donneesJournalieres(int $idParametre, string $date)
    {
        $donneesJournalieres = [];
        $commandeSQL = "SELECT * FROM donnees WHERE ID_Parametre = :ID_Parametre AND Date = :Date;";
        $expression = $this->connexionPDO->prepare($commandeSQL);
        $expression->bindParam(":Date", $date, PDO::PARAM_STR);
        $expression->bindParam(":ID_Parametre", $idParametre, PDO::PARAM_STR);

        $expression->execute();

        $resultats = $expression->fetchAll(PDO::FETCH_ASSOC);

        // Si le résultat n'est pas vide, on ajoute la données au tableau.
        if (!empty($resultats)) {
            if ((int)$idParametre === 1) {
                foreach ($resultats as $resultat) {
                    if ($this->uniteTemperature === "F") {
                        $resultat["Valeur"] = $this->celciusVersFahrenheit($resultat["Valeur"]);
                    }
                    $resultat["Valeur"] = round($resultat["Valeur"], 2);
                    $donneesJournalieres[] = $resultat;
                }
            } else {
                foreach ($resultats as $resultat) {
                    if ($this->uniteLongueur === "po") {
                        $resultat["Valeur"] = $this->millimetreVersPouce($resultat["Valeur"]);
                    }
                    $resultat["Valeur"] = round($resultat["Valeur"], 2);
                    $donneesJournalieres[] = $resultat;
                }
            }
        }

        return $donneesJournalieres;
    }

    // Converti la valeur reçue de Celcius vers Fahrenheit.
    private function celciusVersFahrenheit(float $temperature): float
    {
        return round($temperature * 1.8 + 32, 2);
    }

    // Converti la valeur reçue de millimètre vers pouce.
    private function millimetreVersPouce(float $longueur): float
    {
        return round($longueur / 25.4, 2);
    }
}
