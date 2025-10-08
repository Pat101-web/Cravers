<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    var_dump($_FILES['profile_pic']);
    $file = $_FILES['profile_pic'];
    echo "<pre>error code: {$file['error']}\nsize: {$file['size']}\nname: {$file['name']}</pre>";

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $safe = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target = $uploadDir . $safe;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        echo "<p>Moved OK to uploads/{$safe}</p>";
    } else {
        echo "<p>move_uploaded_file returned FALSE. Check permissions and php.ini</p>";
    }
}
?>
<form method="post" enctype="multipart/form-data">
  <input type="file" name="profile_pic" accept="image/*"><br><br>
  <button type="submit">Upload test</button>
</form>
