<?php
define('page','view_product');

?>
<div class="container-fluid" style="padding: 25px 15px; background-color: #fff;">
    <!-- Page Header -->
    <div class="mb-4">
        <h2 style="color: #333; font-weight: 700; margin-bottom: 8px;">
            <i class="bi bi-shop me-2" style="color: #d4af37;"></i>Explore Our Products
        </h2>
        <p style="color: #666; margin-bottom: 0; font-size: 0.95rem;">Discover our premium collection of fine fragrances</p>
    </div>

    <!-- Search & Filter Section -->
    <div class="card mb-4" style="border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <div class="card-body" style="padding: 20px;">
            <form method="get" class="row g-2">
                <div class="col-md-7">
                    <label class="form-label" style="font-weight: 600; color: #333; font-size: 0.9rem;">Search</label>
                    <input name="q" class="form-control" placeholder="Search perfumes or brands..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" style="border-color: #d4af37;">
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-weight: 600; color: #333; font-size: 0.9rem;">Concentration</label>
                    <select name="concentration" class="form-select" style="border-color: #d4af37;">
                        <option value="">All Types</option>
                        <option value="EDP" <?php echo (($_GET['concentration'] ?? '')==='EDP')? 'selected':''; ?>>EDP</option>
                        <option value="EDT" <?php echo (($_GET['concentration'] ?? '')==='EDT')? 'selected':''; ?>>EDT</option>
                        <option value="Parfum" <?php echo (($_GET['concentration'] ?? '')==='Parfum')? 'selected':''; ?>>Parfum</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn w-100" style="background-color: #d4af37; color: #000; font-weight: 600; border: none;">
                        <i class="bi bi-search me-1"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row g-3"> 
      

<?php
include('../admin/conn.php');
// Build filters
$q = mysqli_real_escape_string($con, trim($_GET['q'] ?? ''));
$conc = mysqli_real_escape_string($con, trim($_GET['concentration'] ?? ''));
$where = [];
if ($q !== '') { $where[] = "(pname LIKE '%$q%' OR brand LIKE '%$q%')"; }
if ($conc !== '') { $where[] = "concentration='$conc'"; }
// pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;
$whereSql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';
// total count
$totalRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM product" . $whereSql);
$totalRow = mysqli_fetch_assoc($totalRes);
$total = intval($totalRow['cnt'] ?? 0);
$pages = max(1, ceil($total / $limit));
$sql = "SELECT * FROM `product`" . $whereSql . " LIMIT $limit OFFSET $offset";
$result = mysqli_query($con, $sql);

if(mysqli_num_rows($result) > 0) {
    while($row=mysqli_fetch_assoc($result)){
        $qty = isset($row['stock']) && $row['stock'] !== null ? (int)$row['stock'] : (int)$row['pqty'];
        $stock_color = $qty > 0 ? '#28a745' : '#dc3545';
        $stock_text = $qty > 0 ? 'In Stock' : 'Out of Stock';
?>
    <div class="col-6 col-md-4 col-lg-3">
        <div class="card h-100 product-card" style="border: none; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08); transition: all 0.3s ease;">
            <!-- Image Section -->
            <div style="position: relative; overflow: hidden; height: 220px; background-color: #f8f9fa;">
                <img src="../productimg/<?php echo htmlspecialchars($row['pimg']); ?>" class="w-100 h-100" alt="<?php echo htmlspecialchars($row['pname']); ?>" style="object-fit: cover; transition: transform 0.3s ease;">
                <div style="position: absolute; top: 10px; right: 10px; background-color: #d4af37; color: #000; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">
                    ₹<?php echo htmlspecialchars(number_format($row['pprice'],2)); ?>
                </div>
                <div style="position: absolute; bottom: 10px; left: 10px; background-color: <?php echo $stock_color; ?>; color: white; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">
                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i><?php echo $stock_text; ?>
                </div>
            </div>

            <!-- Card Body -->
            <div class="card-body d-flex flex-column" style="padding: 12px;">
                <h6 class="card-title mb-1" style="color: #333; font-weight: 700; font-size: 0.95rem; min-height: 36px; line-height: 1.2;">
                    <?php echo htmlspecialchars($row['pname']); ?>
                </h6>
                <p style="color: #999; font-size: 0.75rem; margin-bottom: 2px;">
                    <i class="bi bi-building me-1" style="color: #d4af37;"></i>
                    <?php echo htmlspecialchars($row['brand'] ?? 'Brand'); ?>
                </p>
                
                <div class="mb-2" style="flex-grow: 1;">
                    <p style="color: #999; font-size: 0.75rem; margin-bottom: 4px;">
                        <?php echo htmlspecialchars($row['size_ml'] ?? ''); ?>ml · <?php echo htmlspecialchars($row['concentration'] ?? ''); ?>
                    </p>
                    <?php if(!empty($row['scent_notes'])): ?>
                        <p style="color: #b3b3b3; font-size: 0.7rem; margin-bottom: 0; line-height: 1.3;">
                            <i class="bi bi-flower2 me-1" style="color: #d4af37;"></i>
                            <?php echo htmlspecialchars(substr($row['scent_notes'], 0, 40)); ?>...
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Quantity & Buy Section -->
                <form action="purchase.php" method="post" class="mt-auto">
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.75rem; color: #666; font-weight: 500;">Qty</label>
                        <input type="number" value="1" min="1" max="<?php echo $qty; ?>" name="qty" class="form-control" style="border-color: #d4af37; font-size: 0.85rem; padding: 6px;">
                    </div>
                    <input type="hidden" name="pid" value="<?php echo $row['pid']; ?>">
                    <input type="hidden" name="pname" value="<?php echo htmlspecialchars($row['pname']); ?>">
                    <input type="hidden" name="pprice" value="<?php echo $row['pprice']; ?>">    

                    <div class="d-grid gap-2 mb-2">
                        <?php if ($qty > 0): ?>
                            <button type="submit" class="btn btn-sm" style="background-color: #d4af37; color: #000; font-weight: 600; border: none; padding: 8px; border-radius: 6px; font-size: 0.85rem;">
                                <i class="bi bi-cart-plus me-1"></i>Buy Now
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-sm" style="background-color: #ccc; color: #666; font-weight: 600; border: none; padding: 8px; border-radius: 6px; font-size: 0.85rem;" disabled>
                                Out of Stock
                            </button>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Details Button -->
                <a class="btn btn-sm w-100" href="product.php?pid=<?php echo $row['pid']; ?>" style="background-color: #f8f9fa; color: #333; border: 1px solid #d4af37; font-weight: 600; padding: 8px; border-radius: 6px; font-size: 0.85rem; text-decoration: none; transition: all 0.3s ease;">
                    <i class="bi bi-eye me-1"></i>View Details
                </a>
            </div>
        </div>
    </div>
<?php
    }
} else {
    echo '<div class="col-12"><div style="text-align: center; padding: 50px 20px;">
        <i class="bi bi-inbox" style="font-size: 3rem; color: #ddd;"></i>
        <h5 style="color: #666; margin-top: 15px;">No products found</h5>
        <p style="color: #999; margin-bottom: 15px;">Try adjusting your search or filters</p>
        <a href="?" class="btn" style="background-color: #d4af37; color: #000; font-weight: 600; border: none;">Clear Filters</a>
    </div></div>';
}
?>
    </div>

    <!-- Pagination -->
    <?php if($pages > 1): ?>
    <div class="row mt-4"><div class="col-12"><nav aria-label="Page navigation"><ul class="pagination justify-content-center">
    <?php for($p=1;$p<=$pages;$p++): ?>
      <li class="page-item <?php echo $p==$page?'active':'';?>">
        <a class="page-link" href="?page=<?php echo $p;?><?php echo !empty($_GET['q'])? '&q='.urlencode($_GET['q']):'';?><?php echo !empty($_GET['concentration'])? '&concentration='.urlencode($_GET['concentration']):'';?>" style="color: <?php echo $p==$page ? '#d4af37' : '#333'; ?>; <?php echo $p==$page ? 'background-color: #fff; border-color: #d4af37;' : ''; ?>">
            <?php echo $p;?>
        </a>
      </li>
    <?php endfor; ?>
    </ul></nav></div></div>
    <?php endif; ?>
</div>

<style>
    .product-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 25px rgba(212, 175, 55, 0.15) !important;
    }

    .product-card:hover img {
        transform: scale(1.08);
    }

    .page-link.active {
        background-color: #d4af37 !important;
        border-color: #d4af37 !important;
        color: #000 !important;
    }

    .page-link:hover {
        color: #d4af37 !important;
    }
</style>
