<?php
// includes/functions.php
function redirect($url) {
    header("Location: $url");
    exit;
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function formatMoney($amount) {
    return 'ETB ' . number_format($amount, 2);
}
