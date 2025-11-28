<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include email configuration
require_once 'config.php';

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
    'date' => date('Y-m-d H:i:s'),
    'approved' => false,
    'status' => 'pending'
];

// Add to beginning of array
array_unshift($reviews, $newReview);

// Save to file
$result = @file_put_contents($reviewsFile, json_encode($reviews, JSON_PRETTY_PRINT));
if ($result !== false) {
    // Log success
    error_log('Review saved successfully. File size: ' . $result . ' bytes');
    
    // Send email notification to admin
    sendEmailNotification($newReview);
    
    echo json_encode([
        'success' => true,
        'message' => 'Review submitted successfully and is pending approval',
        'review' => $newReview,
        'pending' => true
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

/**
 * Send email notification to admin about new review
 */
function sendEmailNotification($review) {
    // Check if email notifications are enabled
    if (!EMAIL_NOTIFICATIONS_ENABLED) {
        return;
    }
    
    $adminEmail = ADMIN_EMAIL;
    $subject = '🔔 New Review Pending Approval';
    
    // Create star rating display
    $stars = str_repeat('⭐', $review['rating']);
    
    // Email body (HTML)
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
            .review-box { background: white; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #667eea; }
            .rating { color: #ffc107; font-size: 20px; margin: 10px 0; }
            .comment { color: #555; margin: 10px 0; }
            .meta { color: #999; font-size: 14px; }
            .button { display: inline-block; background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
            .footer { text-align: center; margin-top: 20px; color: #999; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>🔔 New Review Submitted</h2>
            </div>
            <div class="content">
                <p>A new review has been submitted and is awaiting your approval.</p>
                
                <div class="review-box">
                    <h3>' . htmlspecialchars($review['name']) . '</h3>
                    <div class="rating">' . $stars . ' (' . $review['rating'] . '/5)</div>
                    <div class="comment">"' . nl2br(htmlspecialchars($review['comment'])) . '"</div>
                    <div class="meta">Submitted on: ' . $review['date'] . '</div>
                </div>
                
                <p style="text-align: center; margin: 20px 0;">
                    <a href="' . SITE_URL . '/admin.html" class="button">Review in Admin Panel</a>
                </p>
                
                <p style="color: #666; font-size: 14px;">
                    <strong>Review ID:</strong> ' . $review['id'] . '<br>
                    <strong>Status:</strong> Pending Approval
                </p>
            </div>
            <div class="footer">
                <p>This is an automated notification from your Review System.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . EMAIL_FROM_NAME . " <" . EMAIL_FROM . ">" . "\r\n";
    $headers .= "Reply-To: " . EMAIL_FROM . "\r\n";
    
    // Send email
    $emailSent = @mail($adminEmail, $subject, $message, $headers);
    
    // Log result
    if ($emailSent) {
        error_log('Email notification sent to: ' . $adminEmail);
    } else {
        error_log('Failed to send email notification to: ' . $adminEmail);
    }
    
    return $emailSent;
}
?>
