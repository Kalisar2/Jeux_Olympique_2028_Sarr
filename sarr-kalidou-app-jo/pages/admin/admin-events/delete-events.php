<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérification si les paramètres nécessaires sont passés
if (isset($_POST['nom_epreuve'], $_POST['date_epreuve'], $_POST['heure_epreuve'])) {
    $nom_epreuve = $_POST['nom_epreuve'];
    $date_epreuve = $_POST['date_epreuve'];
    $heure_epreuve = $_POST['heure_epreuve'];

    // Vérification du token CSRF
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
        try {
            // Vérification de l'existence de l'épreuve
            $query = "SELECT * FROM epreuve WHERE nom_epreuve = :nom_epreuve AND date_epreuve = :date_epreuve AND heure_epreuve = :heure_epreuve";
            $statement = $connexion->prepare($query);
            $statement->bindParam(':nom_epreuve', $nom_epreuve);
            $statement->bindParam(':date_epreuve', $date_epreuve);
            $statement->bindParam(':heure_epreuve', $heure_epreuve);
            $statement->execute();

            if ($statement->rowCount() > 0) {
                // L'épreuve existe, procéder à la suppression
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
            } else {
                $_SESSION['error'] = "Aucune épreuve trouvée à supprimer.";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la suppression : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    } else {
        $_SESSION['error'] = "Token CSRF invalide.";
    }
} else {
    $_SESSION['error'] = "Paramètres manquants.";
}

// Redirection vers la page de gestion des événements
header("Location: manage-events.php");
exit;
?>

        <p>Êtes-vous sûr de vouloir supprimer l'épreuve suivante ?</p>
        <p><strong>Nom de l'Épreuve :</strong> <?php echo htmlspecialchars($event['nom_epreuve'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Sport :</strong> <?php echo htmlspecialchars($event['nom_sport'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Date :</strong> <?php echo htmlspecialchars($event['date_epreuve'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Heure :</strong> <?php echo htmlspecialchars($event['heure_epreuve'], ENT_QUOTES, 'UTF-8'); ?></p>

        <form action="delete-events.php" method="post">
            <input type="hidden" name="nom_epreuve" value="<?php echo htmlspecialchars($event['nom_epreuve'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="date_epreuve" value="<?php echo htmlspecialchars($event['date_epreuve'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="heure_epreuve" value="<?php echo htmlspecialchars($event['heure_epreuve'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit">Confirmer la Suppression</button>
        </form>

        <p><a href="manage-events.php">Annuler</a></p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>
</html>