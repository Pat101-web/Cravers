<?php
// public/search.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../config/db.php";

$q   = isset($_GET['q'])   ? trim($_GET['q'])   : '';
$cat = isset($_GET['cat']) ? (int)$_GET['cat']  : 0;

if ($q !== '' && $cat > 0) {
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE category_id = ? AND (name LIKE ? OR description LIKE ?)");
    $like = "%$q%";
    $stmt->execute([$cat, $like, $like]);
} elseif ($q !== '') {
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE name LIKE ? OR description LIKE ?");
    $like = "%$q%";
    $stmt->execute([$like, $like]);
} elseif ($cat > 0) {
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE category_id = ?");
    $stmt->execute([$cat]);
} else {
    $stmt = $pdo->query("SELECT * FROM restaurants");
}

$restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$html = '';
if (!$restaurants) {
    $html .= '<div class="col-12"><div class="alert alert-secondary">No restaurants match your search.</div></div>';
} else {
    foreach ($restaurants as $res) {
        $name  = htmlspecialchars($res['name']);
        $desc  = htmlspecialchars($res['description']);
        $rating = htmlspecialchars($res['rating']);
        $time  = htmlspecialchars($res['delivery_time']);
        $img   = htmlspecialchars($res['image'] ?: 'placeholder.jpg');
        $id    = (int)$res['id'];

        // IMPORTANT: this path must match what index.php expects.
        $imgPath = "../assets/images/".$img;

        $html .= '
        <div class="col-md-6 mb-3">
          <div class="card shadow-sm">
            <img src="'.$imgPath.'" class="card-img-top" alt="'.$name.'" style="height:200px; object-fit:cover;" onerror="this.onerror=null;this.src=\'../assets/images/placeholder.jpg\';">
            <div class="card-body">
              <h5 class="card-title">'.$name.'</h5>
              <p class="card-text text-muted">'.$desc.'</p>
              <div class="d-flex justify-content-between mb-2">
                <span>⭐ '.$rating.'</span>
                <span>⏱ '.$time.'</span>
              </div>
              <a href="restaurant.php?id='.$id.'" class="btn btn-sm btn-primary">View</a>
            </div>
          </div>
        </div>';
    }
}

header('Content-Type: application/json');
echo json_encode([
    'count' => count($restaurants),
    'html'  => $html
]);
