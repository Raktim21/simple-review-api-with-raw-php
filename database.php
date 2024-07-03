
<?php
// database.php

    $host = '127.0.0.1';
    $db   = 'product_reviews';
    $user = 'root';
    $pass = 'selopia@123A';

    $mysqli = new mysqli($host, $user, $pass, $db);

    if ($mysqli->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
        exit;
    }
?>