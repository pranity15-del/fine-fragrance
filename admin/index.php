<?php
include('header.php');
include('conn.php');

// Get filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($con, $_GET['category']) : '';

// Build query
$sql = "SELECT * FROM `product` WHERE 1=1";
$filter_applied = false;

if (!empty($search)) {
    $sql .= " AND (pname LIKE '%$search%' OR pcompany LIKE '%$search%')";
    $filter_applied = true;
}

if (!empty($category)) {
    $sql .= " AND pitem = '$category'";
    $filter_applied = true;
}

$sql .= " ORDER BY pid DESC";
$result = mysqli_query($con, $sql);

// Get total count
$total_sql = "SELECT COUNT(*) as cnt FROM `product`";
$total_result = mysqli_query($con, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_products = $total_row['cnt'];

// Get categories for filter
$cat_sql = "SELECT DISTINCT pitem FROM `product` ORDER BY pitem";
$cat_result = mysqli_query($con, $cat_sql);
?>

<div class="container-fluid" style="padding: 25px 15px; background-color: #fff;">
    <!-- Page Header -->
    <div class="mb-3">
        <h1 style="color: #333; font-weight: 700; margin-bottom: 4px; font-size: 2rem;">
            <i class="bi bi-box2 me-2" style="color: #d4af37;"></i>All Products
        </h1>
        <p style="color: #666; margin-bottom: 0; font-size: 0.95rem;">
            Total Products: <strong><?php echo $total_products; ?></strong>
        </p>
    </div>

    <!-- Action Buttons -->
    <div class="mb-3 d-flex gap-2 flex-wrap">
        <a href="add_product.php" class="btn" style="background-color: #d4af37; color: #000; font-weight: 600; border: none; padding: 8px 16px; border-radius: 6px; font-size: 0.95rem;">
            <i class="bi bi-plus-circle me-2"></i>Add New Product
        </a>
    </div>

    <!-- Search & Filter Section -->
    <div class="card mb-3" style="border: none; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.08);">
        <div class="card-body" style="padding: 15px;">
            <form method="GET" class="row g-2">
                <div class="col-md-5">
                    <label class="form-label" style="font-weight: 600; color: #333; font-size: 0.9rem;">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="By name or brand..." value="<?php echo htmlspecialchars($search); ?>" style="border-color: #d4af37; font-size: 0.9rem;">
                </div>
                <div class="col-md-5">
                    <label class="form-label" style="font-weight: 600; color: #333; font-size: 0.9rem;">Category</label>
                    <select name="category" class="form-select" style="border-color: #d4af37; font-size: 0.9rem;">
                        <option value="">All Categories</option>
                        <?php while($cat_row = mysqli_fetch_assoc($cat_result)): ?>
                            <option value="<?php echo htmlspecialchars($cat_row['pitem']); ?>" <?php echo ($category == $cat_row['pitem']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat_row['pitem']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn w-100" style="background-color: #d4af37; color: #000; font-weight: 600; border: none; font-size: 0.9rem;">
                        <i class="bi bi-search me-1"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Active Filters Display -->
    <?php if ($filter_applied): ?>
        <div class="alert alert-light border mb-3" style="border-left: 4px solid #d4af37; padding: 12px;">
            <h6 style="color: #d4af37; font-weight: 700; margin-bottom: 8px; font-size: 0.9rem;">
                <i class="bi bi-funnel me-2"></i>Active Filters
            </h6>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <?php if (!empty($search)): ?>
                    <span style="background-color: #f8f9fa; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem;">
                        <strong><?php echo htmlspecialchars($search); ?></strong>
                        <a href="?<?php echo !empty($category) ? 'category='.$category : ''; ?>" style="margin-left: 6px; color: #dc3545; text-decoration: none; font-weight: 700;">✕</a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($category)): ?>
                    <span style="background-color: #f8f9fa; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem;">
                        <strong><?php echo htmlspecialchars($category); ?></strong>
                        <a href="?<?php echo !empty($search) ? 'search='.$search : ''; ?>" style="margin-left: 6px; color: #dc3545; text-decoration: none; font-weight: 700;">✕</a>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Products Grid -->
    <div class="row g-3">
        <?php
        if(mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $qty = $row['pqty'];
                $status_color = $qty > 0 ? '#28a745' : '#dc3545';
                $status_text = $qty > 0 ? 'In Stock' : 'Out of Stock';
        ?>
                <div class="col-6 col-sm-6 col-md-4 col-lg-3">
                    <div class="card product-card h-100" style="border: none; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s ease;">
                        <!-- Image Section -->
                        <div style="position: relative; overflow: hidden; height: 180px; background-color: #f8f9fa;">
                            <img src="../productimg/<?php echo htmlspecialchars($row['pimg']); ?>" class="w-100 h-100" alt="<?php echo htmlspecialchars($row['pname']); ?>" style="object-fit: cover; transition: transform 0.3s ease;">
                            <div style="position: absolute; top: 8px; right: 8px; background-color: #d4af37; color: #000; padding: 4px 10px; border-radius: 16px; font-size: 0.75rem; font-weight: 700;">
                                ₹<?php echo htmlspecialchars(number_format($row['pprice'], 2)); ?>
                            </div>
                            <div style="position: absolute; bottom: 8px; left: 8px; background-color: <?php echo $status_color; ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 600;">
                                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i><?php echo $status_text; ?>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body d-flex flex-column" style="padding: 12px;">
                            <h6 class="card-title mb-1" style="color: #333; font-weight: 700; min-height: 36px; font-size: 0.9rem; line-height: 1.2;">
                                <?php echo htmlspecialchars($row['pname']); ?>
                            </h6>
                            
                            <div class="mb-2" style="flex-grow: 1;">
                                <p style="color: #999; font-size: 0.75rem; margin-bottom: 3px;">
                                    <i class="bi bi-tag me-1" style="color: #d4af37;"></i>
                                    <?php echo htmlspecialchars($row['pitem']); ?>
                                </p>
                                <p style="color: #999; font-size: 0.75rem; margin-bottom: 0;">
                                    <i class="bi bi-droplet me-1" style="color: #d4af37;"></i>
                                    <?php echo htmlspecialchars($row['pcompany']); ?>
                                </p>
                            </div>

                            <!-- Stock Info -->
                            <div style="background-color: #f8f9fa; padding: 6px; border-radius: 4px; margin-bottom: 8px;">
                                <p style="color: #666; font-size: 0.7rem; margin-bottom: 0;">
                                    <strong>Stock:</strong> <?php echo $qty; ?>
                                </p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-1">
                                <a href="../user/product.php?pid=<?php echo $row['pid']; ?>" class="btn btn-sm" style="background-color: #d4af37; color: #000; font-weight: 600; border: none; font-size: 0.75rem; padding: 6px;" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                                <a href="update_product.php?pid=<?php echo $row['pid']; ?>" class="btn btn-sm" style="background-color: #007bff; color: white; font-weight: 600; border: none; font-size: 0.75rem; padding: 6px;">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </a>
                                <a href="delete.php?pid=<?php echo $row['pid']; ?>" class="btn btn-sm" style="background-color: #dc3545; color: white; font-weight: 600; border: none; font-size: 0.75rem; padding: 6px;" onclick="return confirm('Are you sure?');">
                                    <i class="bi bi-trash me-1"></i>Delete
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
        ?>
            <div class="col-12">
                <div style="text-align: center; padding: 40px 20px;">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ddd;"></i>
                    <h5 style="color: #666; margin-top: 15px; font-size: 1rem;">No products found</h5>
                    <p style="color: #999; margin-bottom: 15px; font-size: 0.9rem;">
                        <?php echo $filter_applied ? 'Try adjusting your search filters.' : 'Start by adding your first product.'; ?>
                    </p>
                    <a href="add_product.php" class="btn" style="background-color: #d4af37; color: #000; font-weight: 600; border: none; font-size: 0.9rem;">
                        <i class="bi bi-plus-circle me-2"></i>Add Product
                    </a>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
</div>

<style>
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(212, 175, 55, 0.2) !important;
    }

    .product-card:hover img {
        transform: scale(1.08);
    }
</style>

<?php
include('footer.php');
?>
