<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$reviewsFile = 'reviews.json';

// Get all reviews (including pending)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $reviews = [];
    
    if (file_exists($reviewsFile)) {
        $content = file_get_contents($reviewsFile);
        $reviews = json_decode($content, true);
        if (!is_array($reviews)) {
            $reviews = [];
        }
    }
    
    // Separate pending and approved reviews
    $pending = array_filter($reviews, function($review) {
        return !isset($review['approved']) || $review['approved'] === false;
    });
    
    $approved = array_filter($reviews, function($review) {
        return isset($review['approved']) && $review['approved'] === true;
    });
    
    echo json_encode([
        'success' => true,
        'all' => $reviews,
        'pending' => array_values($pending),
        'approved' => array_values($approved),
        'pendingCount' => count($pending),
        'approvedCount' => count($approved)
    ]);
    exit;
}

// Approve or reject a review
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !isset($input['action'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    $reviewId = $input['id'];
    $action = $input['action']; // 'approve', 'reject', or 'delete'
    
    if (!file_exists($reviewsFile)) {
        echo json_encode(['success' => false, 'message' => 'No reviews found']);
        exit;
    }
    
    $content = file_get_contents($reviewsFile);
    $reviews = json_decode($content, true);
    
    if (!is_array($reviews)) {
        echo json_encode(['success' => false, 'message' => 'Invalid reviews data']);
        exit;
    }
    
    $found = false;
    $updatedReviews = [];
    
    foreach ($reviews as $review) {
        if ($review['id'] === $reviewId) {
            $found = true;
            if ($action === 'approve') {
                $review['approved'] = true;
                $review['status'] = 'approved';
                $review['approvedDate'] = date('Y-m-d H:i:s');
                $updatedReviews[] = $review;
            }
            // If action is 'reject' or 'delete', don't add to updatedReviews (remove it)
        } else {
            $updatedReviews[] = $review;
        }
    }
    
    if (!$found) {
        echo json_encode(['success' => false, 'message' => 'Review not found']);
        exit;
    }
    
    // Save updated reviews
    $result = file_put_contents($reviewsFile, json_encode($updatedReviews, JSON_PRETTY_PRINT));
    
    if ($result !== false) {
        $message = 'Review rejected';
        if ($action === 'approve') {
            $message = 'Review approved';
        } elseif ($action === 'delete') {
            $message = 'Review deleted';
        }
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'action' => $action
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update reviews']);
    }
}
?>
