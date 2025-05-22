<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . "entete.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "classes\connexionPDO.php";

$derniereMiseAJour;
$dernierEnregistrement = "0";

// fonction affichante la dernière température enregistrée.
function afficherDerniereTemp(string $uniteTempSelectionnee, string $uniteLongueurSelectionnee, string &$dernierEnregistrement)
{
    $pdo = connexionPDO::getConn($uniteTempSelectionnee, $uniteLongueurSelectionnee);

    $donnees = $pdo->derniereDonnee(1);
    $donnee = $donnees["Valeur"];
    $date = $donnees["Date"];
    $heure = $donnees["Heure"];

    // Si l'usager a sélectionné Fahrenheit, on convertit la température.
    if ($uniteTempSelectionnee === "F") {
        $donnee = $donnee * 1.8 + 32;
    }

    // Message disant le moment du dernier enregistrement de la base de données.
    $dernierEnregistrement = "$date à $heure";

    // Affiche le 'widget' de la température.
    echo "<div class=\"widget\">
    <h3>$donnee &deg;$uniteTempSelectionnee</h3>
    <p>Dernière température enregistrée</p>
    <p>Enregistrée le </br> $date à $heure.</p>
    </div>";
}

// Affiche la denière longueur enregistrée.
function afficherDerniereLongueur(string $uniteTempSelectionnee, string $uniteLongueurSelectionnee, string &$dernierEnregistrement, string|null &$derniereMiseAJour)
{
    $pdo = connexionPDO::getConn($uniteTempSelectionnee, $uniteLongueurSelectionnee);

    $donnees = $pdo->derniereDonnee(2);
    $donnee = $donnees["Valeur"];
    $unite = $donnees["Unite"];
    $date = $donnees["Date"];
    $heure = $donnees["Heure"];

    // Si l'usager a sélectionné l'unité pouce, on convertit la longueur.
    if ($uniteLongueurSelectionnee === "po") {
        $unite = $uniteLongueurSelectionnee;
        $donnee = round($donnee / 25.4, 2);
    }

    // Message disant à quelle heure à été faite la dernière mise à jour.
    $derniereMiseAJour = date("Y-m-d") . " à " . date("H:i:s");
    // S'assure de prendre la date la plus récente.
    if ($dernierEnregistrement < "$date à $heure") {
        // Message disant le moment du dernier enregistrement de la base de données.
        $dernierEnregistrement = "$date à $heure";
    }

    // Affiche le 'widget' des précipitation du jour.
    echo "<div class=\"widget\">
    <h3>$donnee $unite</h3>
    <p>Précipitations de la journée</p>
    <p>Enregistrée le </br> $date à $heure.</p>
</div>";
}

// Affiche les précipitations des 7 derniers jours.
function afficherPrecipitationsSemaine(string $uniteTempSelectionnee, string $uniteLongueurSelectionnee)
{
    $pdo = connexionPDO::getConn($uniteTempSelectionnee, $uniteLongueurSelectionnee);

    $donnees = $pdo->precipitationsSemaine();
    $donnee = $donnees["Valeur"];
    $unite = $donnees["Unite"];

    if ($uniteLongueurSelectionnee === "po") {
        $unite = $uniteLongueurSelectionnee;
        $donnee = round($donnee / 25.4, 2);
    }

    // Affiche le 'widget' des précipitation des 7 derniers jours.
    echo "<div class=\"widget\">
    <h3>$donnee $unite</h3>
    <p>Précipitations des 7 derniers jours</p>
</div>";
}

// Affiche les moments de la dernière recharge de la page et du dernier enregistrement dans la base de données.
function derniereMiseAJour(string $dernierEnregistrement, string $derniereMiseAJour)
{
    echo "<div class=\"miseajour\">
    <p>Dernière mise à jour de la page : $derniereMiseAJour</p>
    <p>Dernières données enregistrées : $dernierEnregistrement</p>
    </div>";
}
?>

<!-- Si la connexion à la base de données est réussie, on affiche les données. -->
<?php if ($connexionOK) : ?>
    <!-- Affichage des 'widgets'. -->
    <?php afficherDerniereTemp($uniteTempSelectionnee, $uniteLongueurSelectionnee, $dernierEnregistrement) ?>
    <?php afficherDerniereLongueur($uniteTempSelectionnee, $uniteLongueurSelectionnee, $dernierEnregistrement, $derniereMiseAJour) ?>
    <?php afficherPrecipitationsSemaine($uniteTempSelectionnee, $uniteLongueurSelectionnee) ?>

    <div id="boutonRecharge" class="row">
        <!-- Formulaire pour recharger la page. -->
        <form method="post" action="accueil.php">
            <button type="submit">Recharger les données</button>
        </form>
    </div>
    <div class="row">
        <!-- Affichage de la dernière mise à jour de la page et du dernier enregistrement dans la base de données. -->
        <div class="miseajour">
            <p>Dernière mise à jour de la page : <?= $derniereMiseAJour ?></p>
            <p>Dernières données enregistrées : <?= $dernierEnregistrement ?></p>
        </div>
    </div>
<?php else : ?>
    <!-- Si la connexion n'a pas fonctionnée, on affiche l'erreur. -->
    <?php
    $messageErreur = ERREUR_CONN_BD . $pdo->getMessageErreur();
    ?>

    <body onload="afficherAlerte('<?= $messageErreur ?>')" />
<?php endif; ?>
</div>
</body>

</html>