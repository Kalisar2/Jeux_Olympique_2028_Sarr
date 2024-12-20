<?php
session_start();

// Liste des utilisateurs et mots de passe hachés
$users = [
    'Kalidou' => password_hash('123456', PASSWORD_DEFAULT),
    'admin' => password_hash('admin123', PASSWORD_DEFAULT),
    'john_doe' => password_hash('password123', PASSWORD_DEFAULT),
    'jane_doe' => password_hash('pass123', PASSWORD_DEFAULT),

];

// Vérification de la méthode POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Vérification des identifiants
    if (isset($users[$login]) && password_verify($password, $users[$login])) {
        $_SESSION['login'] = $login; // Stocker le nom d'utilisateur dans la session
        header('Location: manage-sports.php');   // Rediriger vers la page d'accueil
        exit();
    } else {
        // Redirection avec un message d'erreur
        $error = urlencode("Nom d'utilisateur ou mot de passe incorrect.");
        header("Location: index.php?error=$error");
        exit();
    }
} else {
    // Si quelqu'un essaie d'accéder directement à ce fichier
    header('Location: index.php');
    exit();
}

// Afficher les erreurs en PHP
//  error_reporting(E_ALL);
// ini_set("display_errors", 1);

?>
