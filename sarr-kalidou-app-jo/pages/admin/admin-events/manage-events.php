<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Traitement des actions Modifier et Supprimer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        // Vérifiez si les clés existent dans $_POST
        if (isset($_POST['nom_epreuve'], $_POST['date_epreuve'], $_POST['heure_epreuve'], $_POST['csrf_token'])) {
            $nom_epreuve = $_POST['nom_epreuve'];
            $date_epreuve = $_POST['date_epreuve'];
            $heure_epreuve = $_POST['heure_epreuve'];
            $csrf_token = $_POST['csrf_token'];

            // Vérifiez le token CSRF
            if ($csrf_token === $_SESSION['csrf_token']) {
                try {
                    // Requête pour supprimer l'épreuve
                    $query = "DELETE FROM epreuve WHERE nom_epreuve = :nom_epreuve AND date_epreuve = :date_epreuve AND heure_epreuve = :heure_epreuve";
                    $statement = $connexion->prepare($query);
                    $statement->bindParam(':nom_epreuve', $nom_epreuve);
                    $statement->bindParam(':date_epreuve', $date_epreuve);
                    $statement->bindParam(':heure_epreuve', $heure_epreuve);
                    $statement->execute();

                    // Vérifiez si la suppression a réussi
                    if ($statement->rowCount() > 0) {
                        $_SESSION['success'] = "Épreuve supprimée avec succès.";
                    } else {
                        $_SESSION['error'] = "Aucune épreuve trouvée avec ces détails.";
                    }
                } catch (PDOException $e) {
                    $_SESSION['error'] = "Erreur lors de la suppression : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                }
            } else {
                $_SESSION['error'] = "Token CSRF invalide.";
            }
        } else {
            $_SESSION['error'] = "Données manquantes pour la suppression.";
        }
    }
}

// Affichage des messages de succès ou d'erreur
if (isset($_SESSION['success'])) {
    echo "<p style='color: green;'>" . htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') . "</p>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo "<p style='color: red;'>" . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . "</p>";
    unset($_SESSION['error']);
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
    <link rel="stylesheet" href="../../../css/manage.css">
    <link rel="shortcut icon" href="../../../img/favicon.ico" type="image/x-icon">
    <title>Gestion des Épreuves - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Gestion des Épreuves</h1>
        <form action="add-events.php" method="post" onsubmit="">
            <input type="submit" value="Ajouter un evénement">
        </form>

        <!-- Tableau des épreuves -->
        <?php
        try {
            // Requête pour récupérer la liste des épreuves depuis la base de données
            $query = "SELECT e.nom_epreuve, s.nom_sport, e.date_epreuve, e.heure_epreuve
                      FROM epreuve e
                      INNER JOIN sport s ON e.id_sport = s.id_sport
                      ORDER BY e.date_epreuve, e.heure_epreuve";
            $statement = $connexion->prepare($query);
            $statement->execute();

            // Vérifier s'il y a des résultats
            if ($statement->rowCount() > 0) {
                echo "<table><tr><th>Épreuve</th><th>Sport</th><th>Date</th><th>Heure</th><th>Modifier</th><th>Supprimer</th></tr>";

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nom_epreuve'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_sport'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['date_epreuve'], ENT_QUOTES, 'UTF-8') . "</td>";
                    
                    // Formater l'heure pour ne pas afficher les secondes
                    $heureFormatee = date("H:i", strtotime($row['heure_epreuve']));
                    echo "<td>" . htmlspecialchars($heureFormatee, ENT_QUOTES, 'UTF-8') . "</td>";
                    
                    // Bouton Modifier
                    echo "<td>
                            <form action='modify-events.php' method='get' style='display:inline;'>
                                <input type='hidden' name='nom_epreuve' value='" . htmlspecialchars($row['nom_epreuve'], ENT_QUOTES, 'UTF-8') . "'>
                                <button type='submit'>Modifier</button>
                            </form>
                        </td>";

                    // Bouton Supprimer
                    echo "<td>
                            <form action='' method='post' style='display:inline;'>
                                <input type='hidden' name='nom_epreuve' value='" . htmlspecialchars($row['nom_epreuve'], ENT_QUOTES, 'UTF-8') . "'>
                                <input type='hidden' name='date_epreuve' value='" . htmlspecialchars($row['date_epreuve'], ENT_QUOTES, 'UTF-8') . "'>
                                <input type='hidden' name='heure_epreuve' value='" . htmlspecialchars($row['heure_epreuve'], ENT_QUOTES, 'UTF-8') . "'>
                                <input type='hidden' name='action' value='delete'>
                                <input type='hidden' name='csrf_token' value='" . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . "'>
                                <button type='submit' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer cette épreuve ?\");'>Supprimer</button>
                            </form>
                          </td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Aucune épreuve trouvée.</p>";
            }
        } catch (PDOException $e) {
            echo "Erreur lors de la récupération des épreuves : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
        ?>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>
</html>