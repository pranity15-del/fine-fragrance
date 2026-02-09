<?php
include('header.php');
include('conn.php');
if(!isset($_SESSION['is_login'])){ header('location:login.php'); exit; }
$messages = [];
$results = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $action = $_POST['action'] ?? 'search';
    $apiKey = trim($_POST['api_key'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $query = trim($_POST['query'] ?? '');
    if ($action === 'search'){
        if (empty($apiKey) || empty($query)) { $messages[] = ['type'=>'danger','text'=>'API key and query required']; }
        else {
            $url = 'https://api.pexels.com/v1/search?query=' . urlencode($query) . '&per_page=8&page=1';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: $apiKey"]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            $res = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if (!$res){ $messages[] = ['type'=>'danger','text'=>'API error: '.$err]; }
            else { $j = json_decode($res, true); $results = $j['photos'] ?? []; }
        }
    } elseif ($action === 'import') {
        // import selected urls for a SKU
        $selected = $_POST['selected_urls'] ?? [];
        if (empty($sku) || empty($selected)) { $messages[] = ['type'=>'danger','text'=>'SKU and at least one image required']; }
        else {
            $res = mysqli_query($con, "SELECT pid FROM product WHERE sku='".mysqli_real_escape_string($con,$sku)."' LIMIT 1");
            if (!$res || mysqli_num_rows($res)==0) { $messages[] = ['type'=>'danger','text'=>'No product found with SKU']; }
            else {
                $row = mysqli_fetch_assoc($res); $pid = (int)$row['pid'];
                $downloaded = [];
                foreach ($selected as $u){
                    $u = trim($u);
                    if (empty($u)) continue;
                    $ext = pathinfo(parse_url($u, PHP_URL_PATH), PATHINFO_EXTENSION);
                    if (!preg_match('/^(jpe?g|png|gif|webp)$/i', $ext)) { $ext = 'jpg'; }
                    $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $target = __DIR__ . '/../productimg/' . $filename;
                    $ch = curl_init($u);
                    $fp = fopen($target, 'wb');
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Fine&Fragrance-pexels-importer/1.0');
                    $ok = curl_exec($ch);
                    $err = curl_error($ch);
                    curl_close($ch);
                    fclose($fp);
                    if ($ok && filesize($target) > 100) {
                        mysqli_query($con, "INSERT INTO product_images (product_id, filename, is_primary) VALUES ($pid, '".mysqli_real_escape_string($con,$filename)."', 0)");
                        $downloaded[] = $filename;
                    } else {
                        if (file_exists($target)) unlink($target);
                        $messages[] = ['type'=>'warning','text'=>"Failed to download $u: $err"]; 
                    }
                }
                if (count($downloaded)>0){
                    // set pimg if empty
                    $r2 = mysqli_query($con, "SELECT pimg FROM product WHERE pid=$pid"); $rrow = mysqli_fetch_assoc($r2);
                    if (empty($rrow['pimg'])){
                        $first = mysqli_real_escape_string($con, $downloaded[0]);
                        mysqli_query($con, "UPDATE product SET pimg='$first' WHERE pid=$pid");
                        mysqli_query($con, "UPDATE product_images SET is_primary=1 WHERE product_id=$pid AND filename='$first' LIMIT 1");
                    }
                    $messages[] = ['type'=>'success','text'=>'Imported '.count($downloaded).' images for SKU '.$sku];
                }
            }
        }
    }
}
?>
<div class="container" style="margin-top:100px; max-width:1000px;">
  <h2>Pexels Image Search & Import</h2>
  <?php foreach($messages as $m): ?>
    <div class="alert alert-<?php echo htmlspecialchars($m['type']); ?>"><?php echo htmlspecialchars($m['text']); ?></div>
  <?php endforeach; ?>
  <form method="post" class="row g-2 mb-3">
    <div class="col-md-4"><input class="form-control" name="api_key" placeholder="Pexels API Key" value="<?php echo htmlspecialchars($_POST['api_key'] ?? ''); ?>"></div>
    <div class="col-md-4"><input class="form-control" name="query" placeholder="Search query (e.g. amber perfume)" value="<?php echo htmlspecialchars($_POST['query'] ?? ''); ?>"></div>
    <div class="col-md-2"><button class="btn btn-primary" name="action" value="search">Search</button></div>
  </form>

  <?php if (!empty($results)): ?>
    <form method="post">
      <input type="hidden" name="api_key" value="<?php echo htmlspecialchars($_POST['api_key'] ?? ''); ?>">
      <div class="mb-2"><label>SKU to assign images to</label><input class="form-control" name="sku" placeholder="SKU (e.g. FF-AMB-50)" value="<?php echo htmlspecialchars($_POST['sku'] ?? ''); ?>"></div>
      <div class="row">
        <?php foreach($results as $r): ?>
          <div class="col-md-3 mb-3">
            <div class="card">
              <img src="<?php echo htmlspecialchars($r['src']['medium']); ?>" class="card-img-top">
              <div class="card-body small">
                <input type="checkbox" name="selected_urls[]" value="<?php echo htmlspecialchars($r['src']['original']); ?>"> Select<br>
                <small class="text-muted"><?php echo htmlspecialchars($r['photographer'] ?? ''); ?></small>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <button class="btn btn-success" name="action" value="import">Import Selected</button>
    </form>
  <?php endif; ?>

  <p class="muted-small mt-3">Note: Images are downloaded to <code>productimg/</code>. Ensure you comply with Pexels license and attribution policies where required.</p>
</div>
<?php include('footer.php'); ?>