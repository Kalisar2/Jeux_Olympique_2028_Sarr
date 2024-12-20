<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'épreuve est passé
if (!isset($_GET['nom_epreuve'])) {
    echo "Aucune épreuve spécifiée.";
    exit();
}

// Récupérer les détails de l'épreuve à modifier
$nom_epreuve = $_GET['nom_epreuve'];
$query = "SELECT * FROM epreuve WHERE nom_epreuve = :nom_epreuve";
$statement = $connexion->prepare($query);
$statement->bindParam(':nom_epreuve', $nom_epreuve, PDO::PARAM_STR);
$statement->execute();
$epreuve = $statement->fetch(PDO::FETCH_ASSOC);

if (!$epreuve) {
    echo "Épreuve non trouvée.";
    exit();
}

// Traitement de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveau_nom = $_POST['nom_epreuve'];
    $nouvelle_date = $_POST['date_epreuve'];
    $nouvelle_heure = $_POST['heure_epreuve'];
    $id_sport = $_POST['id_sport']; // Assurez-vous d'avoir un moyen de récupérer l'ID du sport

    // Mise à jour de l'épreuve
    $updateQuery = "UPDATE epreuve SET nom_epreuve = :nom_epreuve, date_epreuve = :date_epreuve, heure_epreuve = :heure_epreuve, id_sport = :id_sport WHERE nom_epreuve = :ancien_nom_epreuve";
    $updateStatement = $connexion->prepare($updateQuery);
    $updateStatement->bindParam(':nom_epreuve', $nouveau_nom, PDO::PARAM_STR);
    $updateStatement->bindParam(':date_epreuve', $nouvelle_date, PDO::PARAM_STR);
    $updateStatement->bindParam(':heure_epreuve', $nouvelle_heure, PDO::PARAM_STR);
    $updateStatement->bindParam(':id_sport', $id_sport, PDO::PARAM_INT);
    $updateStatement->bindParam(':ancien_nom_epreuve', $nom_epreuve, PDO::PARAM_STR);
    $updateStatement->execute();

    // Redirection après la mise à jour
    header('Location: manage-events.php');
    exit();
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
        <title>Modifier Épreuve</title>
    </head>
<body>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="../admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="../admin-places/manage-places.php">Gestion Lieux</a></li>
                <li><a href="../admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-events/manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../admin-gender/manage-gender.php">Gestion Genres</a></li>
                <li><a href="../pages/logout.php">Déconnexion</a></li>
            </ul>
        </nav>




<main>
<h1>Modifier une Épreuve</h1>
<form action="" method="post">
    <label for="nom_epreuve">Nom de l'Épreuve :</label>
    <input type="text" name="nom_epreuve" id="nom_epreuve" value="<?php echo htmlspecialchars($epreuve['nom_epreuve'], ENT_QUOTES, 'UTF-8'); ?>" required>

    <label for="date_epreuve">Date de l'Épreuve :</label>
    <input type="date" name="date_epreuve" id="date_epreuve" value="<?php echo htmlspecialchars($epreuve['date_epreuve'], ENT_QUOTES, 'UTF-8'); ?>" required>

    <label for="heure_epreuve">Heure de l'Épreuve :</label>
    <input type="time" name="heure_epreuve" id="heure_epreuve" value="<?php echo htmlspecialchars($epreuve['heure_epreuve'], ENT_QUOTES, 'UTF-8'); ?>" required>

    <label for="id_sport">Sport :</label>
    <select name="id_sport" id="id_sport" required>
        <!--  liste avec les sports disponibles -->
        <?php
        $sportsQuery = "SELECT * FROM sport";
        $sportsStatement = $connexion->prepare($sportsQuery);
        $sportsStatement->execute();
        while ($sport = $sportsStatement->fetch(PDO::FETCH_ASSOC)) {
            echo "<option value='" . htmlspecialchars($sport['id_sport'], ENT_QUOTES, 'UTF-8') . "' " . ($sport['id_sport'] == $epreuve['id_sport'] ? 'selected' : '') . ">" . htmlspecialchars($sport['nom_sport'], ENT_QUOTES, 'UTF-8') . "</option>";
        }
        ?>
    </select>

                <!--  Comment refaire le bouton  <input type="submit" value="Mettre à jour l'épreuve">  -->


    <input type="submit" value="Modifier l'épreuve">
    </form>

    <p class="paragraph-link">
        <a class="link-home" href="manage-events.php">Retour à la gestion des épreuves</a>

    </p>
</main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
    
</body>
</html>