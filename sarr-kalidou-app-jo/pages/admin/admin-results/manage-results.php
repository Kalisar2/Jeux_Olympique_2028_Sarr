<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Fonction pour vérifier le token CSRF
function checkCSRFToken() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('Token CSRF invalide.');
        }
    }
}

// Générer un token CSRF si ce n'est pas déjà fait
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère un token CSRF
}

// Traitement des actions Modifier et Supprimer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRFToken();

    if (isset($_POST['action'])) {
        $id_athlete = $_POST['id_athlete'];
        $id_epreuve = isset($_POST['id_epreuve']) ? $_POST['id_epreuve'] : null; // Vérifiez si id_epreuve est défini

        if ($_POST['action'] === 'delete') {
            if ($id_epreuve === null) {
                die('id_epreuve non défini pour la suppression.');
            }
            // Code pour supprimer un résultat
            $deleteQuery = "DELETE FROM participer WHERE id_athlete = :id_athlete AND id_epreuve = :id_epreuve";
            $deleteStatement = $connexion->prepare($deleteQuery);
            $deleteStatement->bindParam(':id_athlete', $id_athlete, PDO::PARAM_INT);
            $deleteStatement->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);
            if ($deleteStatement->execute()) {
                $_SESSION['success'] = "Résultat supprimé avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression du résultat.";
            }

            header('Location: manage-results.php'); // Redirige après l'action
            exit();
        } elseif ($_POST['action'] === 'modify') {
            if ($id_epreuve === null) {
                die('id_epreuve non défini pour la modification.');
            }
            // Rediriger vers la page de modification avec les paramètres nécessaires
            header("Location: modify-results.php?id_athlete=$id_athlete&id_epreuve=$id_epreuve");
            exit();
        }
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
    <title>Gestion des Résultats - Jeux Olympiques - Los Angeles 2028</title>


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
            <h1>Gestion des Résultats</h1>
        
            <form action="add-results.php" method="post" onsubmit="">
            <input type="submit" value="Ajouter un Resultat">
        </form>

            <!-- Affichage des messages de succès ou d'erreur -->
            <?php
            if (isset($_SESSION['success'])) {
                echo "<p class='success'>" . htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') . "</p>";
                unset($_SESSION['success']); // Supprime le message après l'affichage
            }

            if (isset($_SESSION['error'])) {
                echo "<p class='error'>" . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . "</p>";
                unset($_SESSION['error']); // Supprime le message après l'affichage
            }
            ?>

            <!-- Tableau des résultats -->
            <?php
            try {
                // Requête pour récupérer la liste des résultats depuis la base de données
                $query = "
                    SELECT a.id_athlete, a.nom_athlete, a.prenom_athlete, s.nom_sport, e.id_epreuve, e.nom_epreuve, p.resultat
                    FROM participer p
                    INNER JOIN athlete a ON p.id_athlete = a.id_athlete
                    INNER JOIN epreuve e ON p.id_epreuve = e.id_epreuve
                    INNER JOIN sport s ON e.id_sport = s.id_sport
                    ORDER BY e.date_epreuve, e.heure_epreuve, p.resultat;
                ";
                $statement = $connexion->prepare($query);
                $statement->execute();

                // Vérifier s'il y a des résultats
                if ($statement->rowCount() > 0) {
                    echo "<table>
                            <tr>
                                <th>Nom de l'Athlète</th>
                                <th>Prénom de l'Athlète</th>
                                <th>Sport</th>
                                <th>Épreuve</th>
                                <th>Résultat</th>
                                <th>Modifier</th>
                                <th>Supprimer</th>
                            </tr>";

                    // Afficher les données dans un tableau
                    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['nom_athlete'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['prenom_athlete'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['nom_sport'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['nom_epreuve'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['resultat'], ENT_QUOTES, 'UTF-8') . "</td>";
                        
                        // Bouton Modifier
                        echo "<td>
                                <form action='' method='post' style='display:inline;'>
                                    <input type='hidden' name='action' value='modify'>
                                    <input type='hidden' name='id_athlete' value='" . htmlspecialchars($row['id_athlete'], ENT_QUOTES, 'UTF-8') . "'>
                                    <input type='hidden' name='id_epreuve' value='" . htmlspecialchars($row['id_epreuve'], ENT_QUOTES, 'UTF-8') . "'>
                                    <input type='hidden' name='csrf_token' value='" . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . "'>
                                    <button type='submit'>Modifier</button>
                                </form>
                              </td>";
                        
                        // Bouton Supprimer
                        echo "<td>
                                <form action='' method='post' style='display:inline;'>
                                    <input type='hidden' name='action' value='delete'>
                                    <input type='hidden' name='id_athlete' value='" . htmlspecialchars($row['id_athlete'], ENT_QUOTES, 'UTF-8') . "'>
                                    <input type='hidden' name='id_epreuve' value='" . htmlspecialchars($row['id_epreuve'], ENT_QUOTES, 'UTF-8') . "'>
                                    <input type='hidden' name='csrf_token' value='" . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . "'>
                                    <button type='submit' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer ce résultat ?\");'>Supprimer</button>
                                </form>
                              </td>";
                        
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>Aucun résultat trouvé.</p>";
                }
            } catch (PDOException $e) {
                echo "Erreur : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
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