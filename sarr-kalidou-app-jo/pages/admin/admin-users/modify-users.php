<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'utilisateur est fourni dans l'URL
if (!isset($_GET['id_utilisateur'])) {
    $_SESSION['error'] = "ID de l'utilisateur manquant.";
    header("Location: manage-users.php");
    exit();
}

$id_utilisateur = filter_input(INPUT_GET, 'id_utilisateur', FILTER_VALIDATE_INT);

// Vérifiez si l'ID de l'utilisateur est un entier valide
if (!$id_utilisateur) {
    $_SESSION['error'] = "ID de l'utilisateur invalide.";
    header("Location: manage-users.php");
    exit();
}

// Vider les messages de succès précédents
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}

// Récupérez les informations de l'utilisateur pour affichage dans le formulaire
try {
    $queryUtilisateur = "SELECT nom_utilisateur FROM utilisateur WHERE id_utilisateur = :id_utilisateur";
    $statementUtilisateur = $connexion->prepare($queryUtilisateur);
    $statementUtilisateur->bindParam(":id_utilisateur", $id_utilisateur, PDO::PARAM_INT);
    $statementUtilisateur->execute();

    if ($statementUtilisateur->rowCount() > 0) {
        $utilisateur = $statementUtilisateur->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Utilisateur non trouvé.";
        header("Location: manage-users.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    header("Location: manage-users.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nom_utilisateur = filter_input(INPUT_POST, 'nom_utilisateur', FILTER_SANITIZE_STRING);

    // Vérifiez si le champ est vide
    if (empty($nom_utilisateur)) {
        $_SESSION['error'] = "Tous les champs doivent être remplis.";
        header("Location: modify-users.php?id_utilisateur=$id_utilisateur");
        exit();
    }

    try {
        // Requête pour vérifier si le nom d'utilisateur existe déjà
        $queryCheck = "SELECT id_utilisateur FROM utilisateur WHERE nom_utilisateur = :nom_utilisateur AND id_utilisateur <> :id_utilisateur";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nom_utilisateur", $nom_utilisateur, PDO::PARAM_STR);
        $statementCheck->bindParam(":id_utilisateur", $id_utilisateur, PDO::PARAM_INT);
        $statementCheck->execute();

        // Exécutez la requête
        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'utilisateur existe déjà.";
            header("Location: manage-users.php");
            exit();
        }

        // Requête pour mettre à jour l'utilisateur
        $queryUpdate = "UPDATE utilisateur SET nom_utilisateur = :nom_utilisateur WHERE id_utilisateur = :id_utilisateur";
        $statementUpdate = $connexion->prepare($queryUpdate);
        $statementUpdate->bindParam(":nom_utilisateur", $nom_utilisateur, PDO::PARAM_STR);
        $statementUpdate->bindParam(":id_utilisateur", $id_utilisateur, PDO::PARAM_INT);
        
        if ($statementUpdate->execute()) {
            $_SESSION['success'] = "L'utilisateur a été modifié avec succès.";
            header("Location: manage-users.php");  
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de l'utilisateur.";
            header("Location: modify-users.php?id_utilisateur=$id_util ```php
_utilisateur");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-users.php?id_utilisateur=$id_utilisateur");
        exit();
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
    <link rel="shortcut icon" href="../../../img/favicon.ico" type="image/x-icon">
    <title>Modifier un Résultat - Jeux Olympiques - Los Angeles 2028</title>
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
                <li><a href="../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Modifier un Résultat</h1>
        
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p style="color: green;">' . htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['success']);
        }
        ?>

        <form action="modify-users.php?id_utilisateur=<?php echo $id_utilisateur; ?>" method="post" 
        onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet utilisateur ?')">
            <label for="nom_utilisateur">Nom d'utilisateur :</label>
            <input type="text" name="nom_utilisateur" id="nom_utilisateur" 
            value="<?php echo htmlspecialchars($utilisateur['nom_utilisateur'], ENT_QUOTES, 'UTF-8'); ?>" required>
            <input type="submit" value="Modifier l'utilisateur">
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="manage-users.php">Retour à la gestion des utilisateurs</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>
</html>