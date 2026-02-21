<?php
// includes/functions.php
// Make sure NO whitespace before <?php

/**
 * Safe redirect function that checks if headers are already sent
 * @param string $url The URL to redirect to
 */
function redirect($url) {
    // Check if headers have already been sent
    if (!headers_sent()) {
        // If no headers sent, use PHP header redirect
        header("Location: " . $url);
    } else {
        // If headers already sent, use JavaScript redirect
        echo "<script>window.location.href='" . $url . "';</script>";
    }
    exit;
}

/**
 * Alternative redirect method using meta refresh (works everywhere)
 * @param string $url The URL to redirect to
 * @param int $delay Delay in seconds before redirect
 */
function metaRedirect($url, $delay = 0) {
    echo '<meta http-equiv="refresh" content="' . $delay . ';url=' . $url . '">';
    exit;
}

/**
 * Sanitize user input
 * @param string $input The input to sanitize
 * @return string Sanitized input
 */
function sanitize($input) {
    if (is_null($input)) {
        return '';
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Format money amount
 * @param float $amount The amount to format
 * @return string Formatted amount with ETB currency
 */
function formatMoney($amount) {
    return 'ETB ' . number_format((float)$amount, 2);
}

/**
 * Safe way to start a session if not already started
 */
function safeSessionStart() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Generate a random string
 * @param int $length Length of the random string
 * @return string Random string
 */
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Validate phone number (Ethiopian format)
 * @param string $phone Phone number to validate
 * @return bool True if valid
 */
function validatePhone($phone) {
    // Remove any non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Check if it's a valid Ethiopian phone number (9 digits starting with 9)
    return preg_match('/^9[0-9]{8}$/', $phone) === 1;
}

/**
 * Get client IP address
 * @return string IP address
 */
function getClientIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

/**
 * Log error message
 * @param string $message Error message to log
 */
function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
}

/**
 * Check if request is AJAX
 * @return bool True if AJAX request
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Return JSON response for AJAX requests
 * @param mixed $data Data to encode as JSON
 * @param int $statusCode HTTP status code
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
