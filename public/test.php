<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Test de PHP : OK<br>";

// Tester la connexion à la base de données
try {
    $db_config = include '../config/database.php';
    $db_conn = new PDO(
        "mysql:host={$db_config['connections']['mysql']['host']};dbname={$db_config['connections']['mysql']['database']}",
        $db_config['connections']['mysql']['username'],
        $db_config['connections']['mysql']['password']
    );
    echo "Connexion à la base de données : OK<br>";
} catch (Exception $e) {
    echo "Erreur de base de données : " . $e->getMessage() . "<br>";
}

// Tester la mémoire
echo "Limite de mémoire PHP : " . ini_get('memory_limit') . "<br>";
echo "Mémoire utilisée : " . memory_get_usage(true) / 1024 / 1024 . " MB<br>";

phpinfo();
