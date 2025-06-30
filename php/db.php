<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=PepeComics', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    error_log($e->getMessage(), 3, __DIR__ . '/php-error.log');
    die('Database connection failed. Please try again later.');
} 