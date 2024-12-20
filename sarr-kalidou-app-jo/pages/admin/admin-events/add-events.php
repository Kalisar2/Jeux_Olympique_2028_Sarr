<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Traitement de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifiez si les clés existent dans $_POST
    $nom_epreuve = isset($_POST['nom_epreuve']) ? $_POST['nom_epreuve'] : null;
    $date_epreuve = isset($_POST['date_epreuve']) ? $_POST['date_epreuve'] : null;
    $heure_epreuve = isset($_POST['heure_epreuve']) ? $_POST['heure_epreuve'] : null;
    $id_sport = isset($_POST['id_sport']) ? $_POST['id_sport'] : null;
    $id_lieu = isset($_POST['id_lieu']) ? $_POST['id_lieu'] : null;

    // Vérifiez que toutes les variables sont définies et non nulles
    if ($nom_epreuve && $date_epreuve && $heure_epreuve && $id_sport && $id_lieu) {
        // Insertion de la nouvelle épreuve dans la base de données
        $insertQuery = "INSERT INTO epreuve (nom_epreuve, date_epreuve, heure_epreuve, id_sport, id_lieu) VALUES (:nom_epreuve, :date_epreuve, :heure_epreuve, :id_sport, :id_lieu)";
        $insertStatement = $connexion->prepare($insertQuery);
        $insertStatement->bindParam(':nom_epreuve', $nom_epreuve, PDO::PARAM_STR);
        $insertStatement->bindParam(':date_epreuve', $date_epreuve, PDO::PARAM_STR);
        $insertStatement->bindParam(':heure_epreuve', $heure_epreuve, PDO::PARAM_STR);
        $insertStatement->bindParam(':id_sport', $id_sport, PDO::PARAM_INT);
        $insertStatement->bindParam(':id_lieu', $id_lieu, PDO::PARAM_INT);
        
        // Exécutez la requête
        if ($insertStatement->execute()) {
            // Redirection après l'ajout
            header('Location: manage-events.php');
            exit();
        } else {
            // Gérer l'erreur d'insertion
            echo "Erreur lors de l'ajout de l'épreuve.";
        }
    } else {
        // Gérer le cas où des champs sont manquants
        
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../css/normalize.css">
    <link rel="stylesheet" href="../../../css/styles-computer.css">
    <link rel="stylesheet" href="../../../css/styles-responsive.css">
    <title>Ajouter Épreuve</title>
</head>
<body>
    <center><h1>Ajouter une Épreuve</h1></center>
    <form action="" method="post">
        <label for="nom_epreuve">Nom de l'Épreuve :</label>
        <input type="text" name="nom_epreuve" id="nom_epreuve" required>

        <label for="date_epreuve">Date de l'Épreuve :</label>
        <input type="date" name="date_epreuve" id="date_epreuve" required>

        <label for="heure_epreuve">Heure de l'Épreuve :</label>
        <input type="time" name="heure_epreuve" id="heure_epreuve" required>

        <label for="id_sport">Sport :</label>
        <select name="id_sport" id="id_sport" required>
            <option value="">Sélectionnez un sport</option>
            <?php
            // Récupérer la liste des sports pour le menu déroulant
            $sportsQuery = "SELECT * FROM sport";
            $sportsStatement = $connexion-> prepare($sportsQuery);
            $sportsStatement->execute();
            while ($sport = $sportsStatement->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='" . htmlspecialchars($sport['id_sport'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($sport['nom_sport'], ENT_QUOTES, 'UTF-8') . "</option>";
            }
            ?>
        </select>

        <label for="id_lieu">Lieu :</label>
        <select name="id_lieu" id="id_lieu" required>
            <option value="">Sélectionnez un lieu</option>
            <?php
            // Récupérer la liste des lieux pour le menu déroulant
            $lieuxQuery = "SELECT * FROM lieu"; // Assurez-vous que la table 'lieu' existe
            $lieuxStatement = $connexion->prepare($lieuxQuery);
            $lieuxStatement->execute();
            while ($lieu = $lieuxStatement->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='" . htmlspecialchars($lieu['id_lieu'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($lieu['nom_lieu'], ENT_QUOTES, 'UTF-8') . "</option>";
            }
            ?>
        </select>

        <input type="submit" value="Ajouter l'épreuve">
    </form>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>
</html>