<?php
session_start();
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: pages/dashboard.php');
    exit;
} else {
    header('Location: pages/login.php');
    exit;
}
