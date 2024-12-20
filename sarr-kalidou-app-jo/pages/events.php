<!DOCTYPE html>
<html lang="fr">
<head>
    
   
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
   
<link rel="stylesheet" href="../css/normalize.css">
    
   
<link rel="stylesheet" href="../css/styles-computer.css">
    <link rel="stylesheet" href="../css/styles-responsive.css">

<link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">
    <title>Calendrier des Événements - Jeux Olympiques - Los Angeles 2028</title>
</head>
<body>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="events.php">Accueil</a></li>
                <li><a href="sports.php">Sports</a></li>
                <li><a href="events">Calendrier des épreuves</a></li>
                <li><a href="results.php">Résultats</a></li>
                <li><a href="login.php">Accès administrateur</a></li>
            </ul>
        </nav>  
</header>
  
<main>
        
        
<h1>Calendrier des Événements</h1>

        

  
<?php
        require_once("../database/database.php");

        try {
            // Requête pour récupérer la liste des événements depuis la base de données
            $query = "SELECT e.nom_epreuve, e.date_epreuve, e.heure_epreuve, s.nom_sport, l.nom_lieu 
                      FROM epreuve e
                      JOIN sport s ON e.id_sport = s.id_sport
                      JOIN lieu l ON e.id_lieu = l.id_lieu
                      ORDER BY e.date_epreuve, e.heure_epreuve";
            $statement = $connexion->prepare($query);
            $statement->execute();

            // Vérifier s'il y a des résultats
            if ($statement->rowCount() > 0) {
                echo "<table>";
                echo "<tr><th class='color'>Épreuve</th><th class='color'>Date</th><th class='color'>Heure</th><th class='color'>Sport</th><th class='color'>Lieu</th></tr>";

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nom_epreuve'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['date_epreuve'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['heure_epreuve'], ENT_QUOTES, 'UTF-8') . "</td>";

                    // Vérifiez que les clés existent avant d'y accéder pour éviter les erreurs
                    $nom_sport = !empty($row['nom_sport']) ? htmlspecialchars($row['nom_sport'], ENT_QUOTES, 'UTF-8') : 'Non spécifié';
                    $nom_lieu = !empty($row['nom_lieu']) ? htmlspecialchars($row['nom_lieu'], ENT_QUOTES, 'UTF-8') : 'Non spécifié';
                    
                    echo "<td>$nom_sport</td>";
                    echo "<td>$nom_lieu</td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucun événement trouvé.</p>";
            }
        } catch (PDOException $e) {
            // Gestion d'erreur améliorée
            echo "<p style='color: red;'>Erreur : Impossible de récupérer les événements. Veuillez réessayer plus tard.</p>";
            error_log("Erreur PDO : " . $e->getMessage()); // Log de l'erreur dans un fichier serveur pour débogage
        }

        // Définir le niveau d'affichage des erreurs (utile en phase de développement)
        error_reporting(E_ALL);
        ini_set("display_errors", 1);
        ?>
        
        
        
 

    
    <p class="paragraph-link">    
        <a class="link-home" href="../index.php">Retour Accueil</a>
    </p>

    
    

 
</main>

<footer>
        
        
<figure>
            
            
<img src="../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        
       
</figure>
    
   
</footer>
</body>

</html>