<?php
// index.php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    redirect('home.php');
} else {
    redirect('login.php');
}
?>
