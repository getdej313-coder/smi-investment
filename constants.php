<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function escape($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>