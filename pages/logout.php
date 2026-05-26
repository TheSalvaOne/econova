<?php
// pages/logout.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion();
audit('logout');
logout_usuario();
header('Location: ' . BASE_URL . '/index.php'); exit;
