<?php
// public/vendor_upload.php
session_start();
header('Content-Type: application/json');

// require DB
require __DIR__ . '/../config/db.php';

if (!isset($_SESSION['vendor_id'])) {
    echo json_encode(['success'=>false,'error'=>'Not logged in']);
    exit;
}

$vendor_id = (int) $_SESSION['vendor_id'];

if (empty($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success'=>false,'error'=>'No file uploaded']);
    exit;
}

$allowed = ['image/jpeg','image/png','image/webp'];
if (!in_array($_FILES['logo']['type'], $allowed)) {
    echo json_encode(['success'=>false,'error'=>'Invalid file type']);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/vendors/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
$filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
$target = $uploadDir . $filename;

if (!move_uploaded_file($_FILES['logo']['tmp_name'], $target)) {
    echo json_encode(['success'=>false,'error'=>'Failed to move file']);
    exit;
}

// update vendors table if present (graceful)
try {
    $stmt = $pdo->prepare("UPDATE vendors SET logo = :logo WHERE id = :id");
    $stmt->execute(['logo'=>$filename,'id'=>$vendor_id]);
} catch (Exception $e) {
    // ignore if vendors table doesn't exist
}

echo json_encode(['success'=>true,'file'=>$filename]);
exit;
