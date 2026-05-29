<?php
// logout.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
intranet_session();
$_SESSION = [];
session_destroy();
header('Location: ' . BASE_URL . '/login.php');
exit;
