<?php
include('conn.php');
// Sanitize inputs
$uid = mysqli_real_escape_string($con, $_POST['update_id'] ?? '');
$pname = mysqli_real_escape_string($con, $_POST['pname'] ?? '');
$brand = mysqli_real_escape_string($con, $_POST['brand'] ?? '');
$size_ml = (int)($_POST['size_ml'] ?? 50);
$concentration = mysqli_real_escape_string($con, $_POST['concentration'] ?? 'EDP');
$sku = mysqli_real_escape_string($con, $_POST['sku'] ?? '');
$scent_notes = mysqli_real_escape_string($con, $_POST['scent_notes'] ?? '');
$pitem = mysqli_real_escape_string($con, $_POST['pitem'] ?? '');
$pcompany = mysqli_real_escape_string($con, $_POST['pcompany'] ?? '');
$stock = (int)($_POST['stock'] ?? ($_POST['pqty'] ?? 0));
$pprice = mysqli_real_escape_string($con, $_POST['pprice'] ?? '');
$pqty = (int)($_POST['pqty'] ?? $stock);
$pamount = mysqli_real_escape_string($con, $_POST['pamount'] ?? '');
$pdescription = mysqli_real_escape_string($con, $_POST['product_description'] ?? '');

// Check if an image was uploaded
$imgUpdated = false;
if (isset($_FILES['pimg']) && !empty($_FILES['pimg']['name'])) {
    $filename = basename($_FILES['pimg']['name']);
    $target_dir = "../productimg/";
    $target_file = $target_dir . $filename;
    if (move_uploaded_file($_FILES['pimg']['tmp_name'], $target_file)) {
        $imgUpdated = true;
    } else {
        // Failed to upload image; proceed without changing image
        $imgUpdated = false;
    }
}

if ($imgUpdated) {
    $sqlq = "UPDATE `product` SET `pname`='$pname',`brand`='$brand',`size_ml`='$size_ml',`concentration`='$concentration',`sku`='$sku',`scent_notes`='$scent_notes',`pitem`='$pitem',`pcompany`='$pcompany',`stock`='$stock',`pqty`='$pqty',`pprice`='$pprice',`pamount`='$pamount',`pdis`='$pdescription',`pimg`='$filename' WHERE pid='$uid'";
} else {
    $sqlq = "UPDATE `product` SET `pname`='$pname',`brand`='$brand',`size_ml`='$size_ml',`concentration`='$concentration',`sku`='$sku',`scent_notes`='$scent_notes',`pitem`='$pitem',`pcompany`='$pcompany',`stock`='$stock',`pqty`='$pqty',`pprice`='$pprice',`pamount`='$pamount',`pdis`='$pdescription' WHERE pid='$uid'";
} 

$result = mysqli_query($con, $sqlq);
if ($result) {
    header('Location: view_product.php');
    exit;
} else {
    echo "Error updating record: " . mysqli_error($con);
}
?>