<?php
// Admin image importer - maps SKU to image URLs and downloads images into productimg/
// Usage: Upload a JSON file with format: [{"sku":"FF-AMB-50","urls":["https://...","https://..."]}, ...]
// Or paste JSON in the textarea. Requires admin login.

include('header.php');
include('conn.php');
if(!isset($_SESSION['is_login'])){ header('location:login.php'); exit; }

$messages = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $json = $_POST['json_data'] ?? '';
    if (!empty($_FILES['json_file']['tmp_name'])){
        $json = file_get_contents($_FILES['json_file']['tmp_name']);
    }
    $data = json_decode($json, true);
    if (!is_array($data)){
        $messages[] = ['type'=>'danger','text'=>'Invalid JSON'];
    } else {
        foreach ($data as $item){
            $sku = trim($item['sku'] ?? '');
            $urls = $item['urls'] ?? [];
            if (empty($sku) || empty($urls) || !is_array($urls)) { $messages[] = ['type'=>'warning','text'=>'Skipping invalid item: missing sku/urls']; continue; }
            // find product by sku
            $sku_safe = mysqli_real_escape_string($con, $sku);
            $res = mysqli_query($con, "SELECT pid, pimg FROM product WHERE sku='$sku_safe' LIMIT 1");
            if (!$res || mysqli_num_rows($res) == 0) { $messages[] = ['type'=>'warning','text'=>"No product found for SKU: $sku"]; continue; }
            $row = mysqli_fetch_assoc($res);
            $pid = (int)$row['pid'];
            $downloaded = [];
            foreach ($urls as $u){
                $u = trim($u);
                if (empty($u)) continue;
                // download image (simple, could be extended)
                $ext = pathinfo(parse_url($u, PHP_URL_PATH), PATHINFO_EXTENSION);
                if (!preg_match('/^(jpe?g|png|gif|webp)$/i', $ext)) { $ext = 'jpg'; }
                $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $target = __DIR__ . '/../productimg/' . $filename;
                $ch = curl_init($u);
                $fp = fopen($target, 'wb');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Fine&Fragrance-importer/1.0');
                $ok = curl_exec($ch);
                $err = curl_error($ch);
                curl_close($ch);
                fclose($fp);
                if ($ok && filesize($target) > 100) {
                    // insert into product_images
                    $fn_safe = mysqli_real_escape_string($con, $filename);
                    mysqli_query($con, "INSERT INTO product_images (product_id, filename, is_primary) VALUES ($pid, '$fn_safe', 0)");
                    $downloaded[] = $filename;
                } else {
                    if (file_exists($target)) unlink($target);
                    $messages[] = ['type'=>'warning','text'=>"Failed to download $u for SKU $sku: $err"]; 
                }
            }
            if (count($downloaded) > 0){
                // if product had no pimg, set first as pimg and mark primary
                if (empty($row['pimg'])){
                    $first = mysqli_real_escape_string($con, $downloaded[0]);
                    mysqli_query($con, "UPDATE product SET pimg='$first' WHERE pid=$pid");
                    mysqli_query($con, "UPDATE product_images SET is_primary=1 WHERE product_id=$pid AND filename='$first' LIMIT 1");
                }
                $messages[] = ['type'=>'success','text'=>"Downloaded " . count($downloaded) . " images for SKU $sku"];
            }
        }
    }
}
?>
<div class="container" style="margin-top:100px; max-width:1000px;">
  <h2>Bulk Image Importer</h2>
  <p class="muted-small">Provide JSON mapping SKU → image URLs. Example JSON below.</p>
  <?php foreach($messages as $m): ?>
    <div class="alert alert-<?php echo htmlspecialchars($m['type']); ?>"><?php echo htmlspecialchars($m['text']); ?></div>
  <?php endforeach; ?>
  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Upload JSON file (optional)</label>
      <input type="file" name="json_file" accept="application/json" class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">Or paste JSON mapping</label>
      <textarea name="json_data" rows="8" class="form-control">[ {"sku":"FF-AMB-50","urls":["https://images.pexels.com/photos/12345/pexels-photo-12345.jpeg"]} ]</textarea>
    </div>
    <button class="btn btn-primary">Import</button>
  </form>
  <hr>
  <p class="muted-small">Tip: Use the CLI script <code>scripts/fetch_images.php</code> to fetch images from Unsplash/Pexels via API using queries and SKU mapping.</p>
</div>
<?php include('footer.php'); ?>