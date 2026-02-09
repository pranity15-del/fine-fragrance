<?php
include('../admin/conn.php');
$pid = intval($_GET['pid'] ?? 0);
if ($pid <= 0) { header('Content-Type: application/json'); echo json_encode(['error'=>'invalid_pid']); exit; }
$pid_safe = mysqli_real_escape_string($con, $pid);
$sql = "SELECT pid, pname, brand, pcompany, pitem, size_ml, concentration, sku, scent_notes, stock, pqty, pprice, pamount, pdis, pimg FROM product WHERE pid=$pid_safe LIMIT 1";
$res = mysqli_query($con, $sql);
if (!$res || mysqli_num_rows($res)==0) { header('Content-Type: application/json'); echo json_encode(['error'=>'not_found']); exit; }
$row = mysqli_fetch_assoc($res);
// fetch images
$imgs = [];
$imgRes = mysqli_query($con, "SELECT filename, is_primary FROM product_images WHERE product_id=$pid_safe ORDER BY is_primary DESC, id ASC");
while ($r = mysqli_fetch_assoc($imgRes)){
    $imgs[] = '/productimg/' . $r['filename'];
}
// fallback to product.pimg if no images
if (empty($imgs) && !empty($row['pimg'])){ $imgs[] = '/productimg/' . $row['pimg']; }

$row['images'] = $imgs;
header('Content-Type: application/json');
echo json_encode($row);
?>