<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include('../admin/conn.php');

// Verify database connection
if (!$con) {
  die("Database connection failed. Please try again later.");
}

$pid = intval($_GET['pid'] ?? 0);
if ($pid <= 0) { 
  header('Location: view_product.php'); 
  exit; 
}

// Fetch product with error handling
$sql = "SELECT * FROM product WHERE pid = $pid LIMIT 1";
$res = mysqli_query($con, $sql);

if (!$res) {
  // Query failed - log error and redirect
  error_log("MySQL Error: " . mysqli_error($con));
  error_log("Query: " . $sql);
  header('Location: view_product.php');
  exit;
}

if (mysqli_num_rows($res) == 0) {
  // Product not found
  header('Location: view_product.php');
  exit;
}

$p = mysqli_fetch_assoc($res);

// Fetch product images
$imgs = [];
$imgSql = "SELECT filename, is_primary FROM product_images WHERE product_id = $pid ORDER BY is_primary DESC, id ASC";
$imgRes = mysqli_query($con, $imgSql);

if ($imgRes && mysqli_num_rows($imgRes) > 0) {
  while ($r = mysqli_fetch_assoc($imgRes)) {
    $imgs[] = $r['filename'];
  }
}

// Fallback to primary product image if no images found
if (empty($imgs) && !empty($p['pimg'])) {
  $imgs[] = $p['pimg'];
}

// If still no images, use a placeholder
if (empty($imgs)) {
  $imgs[] = 'placeholder.png';
}

$stock = isset($p['stock']) && $p['stock'] !== null ? (int)$p['stock'] : (int)$p['pqty'];

include('header.php');
?>

<div class="container mb-5" style="max-width: 1200px;">
  <!-- Breadcrumb Navigation -->
  <nav aria-label="breadcrumb" class="mt-4 mb-4">
    <ol class="breadcrumb" style="background-color: transparent; padding: 0;">
      <li class="breadcrumb-item"><a href="view_product.php" style="color: #d4af37; text-decoration: none;">All Products</a></li>
      <li class="breadcrumb-item"><a href="categories.php?category=<?php echo urlencode($p['pitem']); ?>" style="color: #d4af37; text-decoration: none;"><?php echo htmlspecialchars($p['pitem']); ?></a></li>
      <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($p['pname']); ?></li>
    </ol>
  </nav>

  <div class="row g-5">
    <!-- Product Images Section -->
    <div class="col-lg-6">
      <div class="ratio ratio-1x1" style="overflow: hidden; border-radius: 12px; background-color: #f8f9fa;">
        <div id="productCarousel" class="carousel slide" data-bs-ride="carousel" style="height: 100%;">
          <div class="carousel-inner" style="height: 100%;">
            <?php foreach($imgs as $i => $f): ?>
              <div class="carousel-item <?php echo $i===0 ? 'active' : ''; ?>" style="height: 100%;">
                <img src="../productimg/<?php echo htmlspecialchars($f); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($p['pname']); ?>" style="height: 100%; object-fit: cover;">
              </div>
            <?php endforeach; ?>
          </div>
          <?php if(count($imgs)>1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Product Details Section -->
    <div class="col-lg-6">
      <!-- Product Header -->
      <div class="mb-4">
        <h1 style="color: #333; font-weight: 700; margin-bottom: 8px;">
          <?php echo htmlspecialchars($p['pname']); ?>
        </h1>
        <p style="color: #999; font-size: 1.1rem; margin-bottom: 0;">
          <i class="bi bi-building" style="color: #d4af37;"></i>
          by <strong style="color: #666;"><?php echo htmlspecialchars($p['brand'] ?? 'Unknown'); ?></strong>
        </p>
      </div>

      <!-- Price Section -->
      <div class="mb-4 p-3" style="background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #d4af37;">
        <div class="d-flex align-items-baseline gap-2">
          <h2 style="color: #d4af37; font-weight: 700; margin-bottom: 0;">₹<?php echo htmlspecialchars(number_format($p['pprice'],2)); ?></h2>
          <span style="color: #999; font-size: 0.95rem;"><i class="bi bi-check-circle me-1"></i>Best Price Guaranteed</span>
        </div>
      </div>

      <!-- Product Specifications -->
      <div class="mb-4">
        <h5 style="color: #333; font-weight: 600; margin-bottom: 12px;">
          <i class="bi bi-info-circle me-2" style="color: #d4af37;"></i>Product Details
        </h5>
        <div class="row g-3">
          <div class="col-6">
            <div style="padding: 12px; background-color: #f8f9fa; border-radius: 8px;">
              <p style="color: #999; font-size: 0.9rem; margin-bottom: 4px;">Size</p>
              <p style="color: #333; font-weight: 600; margin-bottom: 0;">
                <?php echo htmlspecialchars($p['size_ml'] ?? 'N/A'); ?> ml
              </p>
            </div>
          </div>
          <div class="col-6">
            <div style="padding: 12px; background-color: #f8f9fa; border-radius: 8px;">
              <p style="color: #999; font-size: 0.9rem; margin-bottom: 4px;">Concentration</p>
              <p style="color: #333; font-weight: 600; margin-bottom: 0;">
                <?php echo htmlspecialchars($p['concentration'] ?? 'N/A'); ?>
              </p>
            </div>
          </div>
          <div class="col-6">
            <div style="padding: 12px; background-color: #f8f9fa; border-radius: 8px;">
              <p style="color: #999; font-size: 0.9rem; margin-bottom: 4px;">Category</p>
              <p style="color: #333; font-weight: 600; margin-bottom: 0;">
                <?php echo htmlspecialchars($p['pitem'] ?? 'N/A'); ?>
              </p>
            </div>
          </div>
          <div class="col-6">
            <div style="padding: 12px; background-color: #f8f9fa; border-radius: 8px;">
              <p style="color: #999; font-size: 0.9rem; margin-bottom: 4px;">Stock</p>
              <p style="color: #333; font-weight: 600; margin-bottom: 0;">
                <?php echo $stock > 0 ? '<span style="color: #28a745;"><i class="bi bi-check-circle me-1"></i>'.$stock.' units</span>' : '<span style="color: #dc3545;"><i class="bi bi-x-circle me-1"></i>Out of Stock</span>'; ?>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Scent Notes -->
      <?php if(!empty($p['scent_notes'])): ?>
        <div class="mb-4">
          <h5 style="color: #333; font-weight: 600; margin-bottom: 12px;">
            <i class="bi bi-flower2 me-2" style="color: #d4af37;"></i>Scent Notes
          </h5>
          <p style="color: #666; line-height: 1.6; background-color: #f8f9fa; padding: 12px; border-radius: 8px;">
            <?php echo nl2br(htmlspecialchars($p['scent_notes'])); ?>
          </p>
        </div>
      <?php endif; ?>

      <!-- Quantity Selector & Action Buttons -->
      <form method="post" action="purchase.php" class="mb-4">
        <input type="hidden" name="pid" value="<?php echo $p['pid']; ?>">
        <input type="hidden" name="pname" value="<?php echo htmlspecialchars($p['pname']); ?>">
        <input type="hidden" name="pprice" value="<?php echo htmlspecialchars($p['pprice']); ?>">
        
        <div class="mb-3">
          <label style="color: #333; font-weight: 600; margin-bottom: 8px; display: block;">
            <i class="bi bi-bag me-2" style="color: #d4af37;"></i>Select Quantity
          </label>
          <div class="d-flex align-items-center gap-2">
            <input type="number" name="qty" value="1" min="1" max="<?php echo $stock; ?>" class="form-control" style="width: 100px; border-color: #d4af37; font-weight: 600; text-align: center;">
            <span style="color: #999; font-size: 0.9rem;">Available: <strong><?php echo $stock; ?></strong> units</span>
          </div>
        </div>

        <div class="d-grid gap-2">
          <?php if ($stock > 0): ?>
            <button type="submit" class="btn btn-lg" style="background-color: #d4af37; color: #000; font-weight: 700; border: none; padding: 12px; border-radius: 8px; transition: all 0.3s ease;">
              <i class="bi bi-cart-plus me-2"></i>Add to Cart & Buy Now
            </button>
          <?php else: ?>
            <button type="button" class="btn btn-lg" style="background-color: #ccc; color: #666; font-weight: 700; border: none; padding: 12px; border-radius: 8px;" disabled>
              <i class="bi bi-x-circle me-2"></i>Out of Stock
            </button>
          <?php endif; ?>
        </div>
      </form>

      <!-- Back Button -->
      <div class="text-center">
        <a href="index.php" class="btn btn-outline-secondary" style="border-color: #d4af37; color: #d4af37;">
          <i class="bi bi-arrow-left me-2"></i>Back to Products
        </a>
      </div>
    </div>
  </div>

  <!-- Additional Info Section -->
  <div class="row mt-5 g-3">
    <div class="col-md-4">
      <div style="padding: 20px; background-color: #f8f9fa; border-radius: 10px; border-top: 3px solid #d4af37;">
        <h6 style="color: #333; font-weight: 700; margin-bottom: 8px;">
          <i class="bi bi-shield-check" style="color: #d4af37;"></i> Authentic Product
        </h6>
        <p style="color: #666; font-size: 0.9rem; margin-bottom: 0;">100% genuine products directly from authorized suppliers</p>
      </div>
    </div>
    <div class="col-md-4">
      <div style="padding: 20px; background-color: #f8f9fa; border-radius: 10px; border-top: 3px solid #d4af37;">
        <h6 style="color: #333; font-weight: 700; margin-bottom: 8px;">
          <i class="bi bi-truck" style="color: #d4af37;"></i> Fast Delivery
        </h6>
        <p style="color: #666; font-size: 0.9rem; margin-bottom: 0;">Quick shipping across the country with tracking</p>
      </div>
    </div>
    <div class="col-md-4">
      <div style="padding: 20px; background-color: #f8f9fa; border-radius: 10px; border-top: 3px solid #d4af37;">
        <h6 style="color: #333; font-weight: 700; margin-bottom: 8px;">
          <i class="bi bi-arrow-counterclockwise" style="color: #d4af37;"></i> Easy Returns
        </h6>
        <p style="color: #666; font-size: 0.9rem; margin-bottom: 0;">Hassle-free returns within 7 days of purchase</p>
      </div>
    </div>
  </div>
</div>

<style>
  .carousel-control-prev, .carousel-control-next {
    background-color: rgba(0,0,0,0.3);
    width: 45px;
    height: 45px;
    border-radius: 4px;
    top: 50%;
    transform: translateY(-50%);
  }

  .carousel-control-prev:hover, .carousel-control-next:hover {
    background-color: #d4af37;
  }

  .breadcrumb-item a:hover {
    color: #b48a18 !important;
  }

  form button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
  }
</style>

<?php include('footer.php'); ?>
