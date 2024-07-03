<?php
    // api.php

    header('Content-Type: application/json');

    // Database connection
    require 'database.php';

    // Check if the request method
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Only POST requests are allowed']);
        exit;
    }

    // Get the raw POST data and handle json
    $post_data = file_get_contents('php://input');
    $data = json_decode($post_data, true);

   // Validation
   $errors = [];

    if (!isset($data['product_id']) || !is_numeric($data['product_id'])) {
        $errors[] = 'Invalid or missing product_id. It must be an integer.';
    }

    if (!isset($data['user_id']) || !is_numeric($data['user_id'])) {
        $errors[] = 'Invalid or missing user_id. It must be an integer.';
    }

    if (!isset($data['rating']) || !is_numeric($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
        $errors[] = 'Invalid or missing rating. It must be a integer number between 1 and 5.';
    }
    
    if (isset($data['review_text']) && !is_string($data['review_text'])) {
        $errors[] = 'Invalid review_text. It must be a string.';
    }



    // Handle image upload if necessary
    // If we want to upload an image, then the submited value must be a form data with a name of "image"

    // $image = null;
    // if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    //     $file_tmp_path = $_FILES['image']['tmp_name'];
    //     $file_name = $_FILES['image']['name'];
    //     $file_size = $_FILES['image']['size'];
    //     $file_type = $_FILES['image']['type'];
    //     $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    //     $allowed_extensions = ['jpg', 'jpeg', 'png'];
    //     if (!in_array($file_ext, $allowed_extensions)) {
    //         $errors[] = 'Invalid image file type. Only .jpg, .jpeg, and .png are allowed.';
    //     } else {
          
    //         $upload_dir = 'uploads/';
    //         $dest_path = $upload_dir . basename($file_name);
    //         if (move_uploaded_file($file_tmp_path, $dest_path)) {
    //             $image = $dest_path;
    //         } else {
    //             $errors[] = 'Failed to move uploaded file.';
    //         }
    //     }
    // } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
    //     $errors[] = 'File upload error.';
    // }



    if (!empty($errors)) {
        http_response_code(400); // Bad Request
        echo json_encode(['errors' => $errors]);
        exit;
    }

    // Sanitize input data
    $product_id = intval($data['product_id']);
    $user_id = intval($data['user_id']);
    $rating = intval($data['rating']);
    $review_text = isset($data['review_text']) ? htmlspecialchars($data['review_text'], ENT_QUOTES, 'UTF-8') : '';

    // Insert the review into the database
    try {
        $stmt = $mysqli->prepare('INSERT INTO reviews (product_id, user_id, review_text, rating) VALUES (?, ?, ?, ?)');
        if ($stmt) {
            $stmt->bind_param('iisi', $product_id, $user_id, $review_text, $rating);
            if ($stmt->execute()) {
                http_response_code(201); 
                echo json_encode(['success' => 'Review submitted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to submit review: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to prepare statement: ' . $mysqli->error]);
        }
    } catch (\Throwable $th) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to submit review: ' . $th->getMessage()]);
    }

    $mysqli->close();
?>
