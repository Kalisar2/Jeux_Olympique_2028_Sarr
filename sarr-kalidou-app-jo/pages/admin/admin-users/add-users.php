<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si un token CSRF existe dans la session, sinon générez-en un
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère un token CSRF sécurisé
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nom_utilisateur = filter_input(INPUT_POST, 'nom_utilisateur', FILTER_SANITIZE_SPECIAL_CHARS);
    $prenom_utilisateur = filter_input(INPUT_POST, 'prenom_utilisateur', FILTER_SANITIZE_SPECIAL_CHARS);
    $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // $_SESSION['error'] = "Token CSRF invalide.";
        header("Location: add-users.php");
        exit();
    }

    // Vérifiez si les champs sont vides
    if (empty($nom_utilisateur) || empty($prenom_utilisateur) || empty($login) || empty($password)) {
        $_SESSION['error'] = "Les champs ne peuvent pas être vides.";
        header("Location: add-users.php");
        exit();
    }

    try {
        // Vérifiez si l'utilisateur existe déjà
        $queryCheck = "SELECT id_utilisateur FROM utilisateur WHERE nom_utilisateur = :nom_utilisateur";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nom_utilisateur", $nom_utilisateur, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'utilisateur existe déjà.";
            header("Location: add-users.php");
            exit();
        } else {
            // Requête pour ajouter un utilisateur
            $query = "INSERT INTO utilisateur (nom_utilisateur, prenom_utilisateur, login, password) VALUES (:nom_utilisateur, :prenom_utilisateur, :login, :password)";
            $statement = $connexion->prepare($query);
            $statement->bindParam(":nom_utilisateur", $nom_utilisateur, PDO::PARAM_STR);
            $statement->bindParam(":prenom_utilisateur", $prenom_utilisateur, PDO::PARAM_STR);
            $statement->bindParam(":login", $login, PDO::PARAM_STR);
            $statement->bindParam(":password", password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR); // Hash le mot de passe

            // Exécutez la requête
            if ($statement->execute()) {
                $_SESSION['success'] = "L'utilisateur a été ajouté avec succès.";
                header("Location: manage-users.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout d'utilisateur.";
                header("Location: add-users.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header("Location: add-users.php");
        exit();
    }
}

// Afficher les erreurs en PHP (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
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
    <title>Ajouter un Utilisateur - Jeux Olympiques - Los Angeles 2028</title>
    <style>
        /* Ajoutez votre style CSS ici */
    </style>
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
        <h1>Ajouter un Utilisateur</h1>
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
        <form action="add-users.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cet utilisateur ?')">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <label for="nom_utilisateur">Nom d'utilisateur :</label>
            <input type="text" name="nom_utilisateur" id="nom_utilisateur" required>

            <label for="prenom_utilisateur">Prénom d'utilisateur :</label>
            <input type="text" name="prenom_utilisateur" id="prenom_utilisateur" required>

            <label for="login">Login :</label>
            <input type="text" name="login" id="login" required>

            <label for="password">Mot de passe :</label>
            <input type="password" name="password" id="password" required>
            <input type="submit" value="Ajouter l'utilisateur">
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