<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Get the last check timestamp from query parameter
$lastCheck = isset($_GET['lastCheck']) ? intval($_GET['lastCheck']) : 0;

// Read reviews from JSON file
$reviewsFile = 'reviews.json';
$reviews = [];

if (file_exists($reviewsFile)) {
    $content = file_get_contents($reviewsFile);
    $allReviews = json_decode($content, true);
    if (!is_array($allReviews)) {
        $allReviews = [];
    }
    
    // Filter only approved reviews
    foreach ($allReviews as $review) {
        if (isset($review['approved']) && $review['approved'] === true) {
            $reviews[] = $review;
        }
    }
}

// If lastCheck is provided, find new reviews
$newReviews = [];
if ($lastCheck > 0) {
    foreach ($reviews as $review) {
        if (isset($review['timestamp']) && $review['timestamp'] > $lastCheck) {
            $newReviews[] = $review;
        }
    }
}

echo json_encode([
    'success' => true,
    'reviews' => $reviews,
    'newReviews' => $newReviews,
    'count' => count($reviews)
]);
?>
