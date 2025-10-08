<?php
// require_once __DIR__ . '/vendor_protect.php'; or include path as needed
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'vendor' || !isset($_SESSION['vendor_id'])) {
    header("Location: vendor_login.php");
    exit;
}
