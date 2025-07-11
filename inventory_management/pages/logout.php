<?php
session_start();
require_once '../includes/functions.php';

logout();
redirect('../pages/login.php');
