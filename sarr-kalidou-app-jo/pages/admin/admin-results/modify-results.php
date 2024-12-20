<?php
session_start();
require_once '../../../database/database.php'; // Fichier pour la connexion à la base de données

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérification du token CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
        // Récupération des données du formulaire
        $id_athlete = $_POST['id_athlete'];
        $id_epreuve = $_POST['id_epreuve'];
        $resultat = $_POST['resultat'];

        // Mise à jour du résultat dans la base de données
        try {
            $query = "UPDATE participer SET resultat = :resultat WHERE id_athlete = :id_athlete AND id_epreuve = :id_epreuve";
            $statement = $connexion->prepare($query);
            $statement->bindParam(':resultat', $resultat);
            $statement->bindParam(':id_athlete', $id_athlete);
            $statement->bindParam(':id_epreuve', $id_epreuve);
            $statement->execute();

            $_SESSION['success'] = "Résultat mis à jour avec succès.";
            header("Location: manage-results.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    } else {
        $_SESSION['error'] = "Token CSRF invalide.";
    }
}

// Récupération des données existantes
if (isset($_GET['id_athlete']) && isset($_GET['id_epreuve'])) {
    $id_athlete = $_GET['id_athlete'];
    $id_epreuve = $_GET['id_epreuve'];

    try {
        // Récupération des données pour pré-remplir le formulaire
        $query = "SELECT a.nom_athlete, a.prenom_athlete, e.nom_epreuve, p.resultat 
                  FROM participer p 
                  INNER JOIN athlete a ON p.id_athlete = a.id_athlete 
                  INNER JOIN epreuve e ON p.id_epreuve = e.id_epreuve 
                  WHERE a.id_athlete = :id_athlete AND e.id_epreuve = :id_epreuve";
        $statement = $connexion->prepare($query);
        $statement->bindParam(':id_athlete', $id_athlete);
        $statement->bindParam(':id_epreuve', $id_epreuve);
        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la récupération des données : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
} else {
    $_SESSION['error'] = "Identifiants d'athlète ou d'épreuve manquants.";
    header("Location: manage-results.php");
    exit;
}

// Récupération de la liste des athlètes
$athletes = [];
try {
    $query = "SELECT id_athlete, nom_athlete, prenom_athlete FROM athlete";
    $statement = $connexion->prepare($query);
    $statement->execute();
    $athletes = $statement->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des athlètes : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

// Récupération de la liste des épreuves
$epreuves = [];
try {
    $query = "SELECT id_epreuve, nom_epreuve FROM epreuve";
    $statement = $connexion->prepare($query);
    $statement->execute();
    $epreuves = $statement->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des épreuves : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
?>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../css/normalize.css">
    <link rel="stylesheet" href="../../../css/styles-computer.css">
    <link rel="stylesheet" href="../../../css/styles-responsive.css">
    <link rel="shortcut icon" href="../../../img/favicon.ico" type="image/x-icon">
    <title>Modifier Résultat</title>
    
</head>
<body>
    <header>
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
    </header>

    <main>
        <h1>Modifier Résultat</h1>

        <?php
        if (isset($_SESSION['error'])) {
            echo "<p class='error'>" . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . "</p>";
            unset($_SESSION['error']);
        }
        ?>

        <form action="modify-results.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="id_athlete">Athlète :</label>
            <select id="id_athlete" name="id_athlete" required>
                <?php foreach ($athletes as $athlete): ?>
                    <option value="<?php echo htmlspecialchars($athlete['id_athlete'], ENT_QUOTES, 'UTF-8'); ?>" 
                        <?php if ($athlete['id_athlete'] == $id_athlete) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($athlete['nom_athlete'] . ' ' . $athlete['prenom_athlete'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="id_epreuve">Épreuve :</label>
            <select id="id_epreuve" name="id_epreuve" required>
                <?php foreach ($epreuves as $epreuve): ?>
                    <option value="<?php echo htmlspecialchars($epreuve['id_epreuve'], ENT_QUOTES, 'UTF-8'); ?>" 
                        <?php if ($epreuve['id_epreuve'] == $id_epreuve) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($epreuve['nom_epreuve'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="resultat">Résultat :</label>
            <input type="text" id="resultat" name="resultat" value="<?php echo htmlspecialchars($result['resultat'], ENT_QUOTES, 'UTF-8'); ?>" required>

            <input type="submit" value="Mettre à jour le résultat">
        </form>

        <p><a href="manage-results.php">Retour à la gestion des résultats</a></p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>
</html>