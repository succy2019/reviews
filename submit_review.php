<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Log for debugging
error_log('Received data: ' . print_r($input, true));

if (!$input || !isset($input['name']) || !isset($input['rating']) || !isset($input['comment'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validate rating
$rating = intval($input['rating']);
if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
    exit;
}

// Read existing reviews
$reviewsFile = 'reviews.json';
$reviews = [];

if (file_exists($reviewsFile)) {
    $content = file_get_contents($reviewsFile);
    $reviews = json_decode($content, true);
    if (!is_array($reviews)) {
        $reviews = [];
    }
}

// Create new review
$newReview = [
    'id' => uniqid(),
    'name' => htmlspecialchars(trim($input['name']), ENT_QUOTES, 'UTF-8'),
    'rating' => $rating,
    'comment' => htmlspecialchars(trim($input['comment']), ENT_QUOTES, 'UTF-8'),
    'timestamp' => time(),
    'date' => date('Y-m-d H:i:s')
];

// Add to beginning of array
array_unshift($reviews, $newReview);

// Save to file
$result = @file_put_contents($reviewsFile, json_encode($reviews, JSON_PRETTY_PRINT));
if ($result !== false) {
    echo json_encode([
        'success' => true,
        'message' => 'Review submitted successfully',
        'review' => $newReview
    ]);
} else {
    $error = error_get_last();
    error_log('Failed to write file: ' . print_r($error, true));
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to save review. Check file permissions.',
        'error' => $error['message'] ?? 'Unknown error'
    ]);
}
?>
