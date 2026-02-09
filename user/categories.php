<?php
define('page','categories');
include('header.php');  
?>
<div class="container mt-5 mb-5">
    <!-- Page Header -->
    <div class="mb-5">
        <h2 class="mb-2" style="color: #000; font-weight: 700;">
            <i class="bi bi-bag me-2" style="color: #d4af37;"></i>Browse Our Collection
        </h2>
        <p class="text-muted">Discover our premium selection of fine fragrances and beauty products</p>
    </div>

    <!-- Category Buttons Section -->
    <div class="mb-5">
        <h5 class="mb-3" style="color: #333; font-weight: 600;">
            <i class="bi bi-tag me-2" style="color: #d4af37;"></i>Select Category
        </h5>
        <div class="d-flex flex-wrap gap-2 pb-4">
            <a href="?" class="btn <?php echo (!isset($_GET['category']) ? 'active' : ''); ?>" style="<?php echo (!isset($_GET['category']) ? 'background-color: #d4af37; color: #000; border-color: #d4af37;' : 'border-color: #d4af37; color: #333; background-color: white;'); ?> font-weight: 600; transition: all 0.3s ease; border-width: 1px; border-style: solid;">
                <i class="bi bi-grid-3x3 me-1"></i>All Categories
            </a>
            <?php
            include('../admin/conn.php');
            
            // Get all distinct categories
            $cat_sql = "SELECT DISTINCT pitem FROM `product` ORDER BY pitem";
            $cat_result = mysqli_query($con, $cat_sql);
            
            $selected_category = isset($_GET['category']) ? $_GET['category'] : '';
            
            while($cat_row = mysqli_fetch_assoc($cat_result)) {
                $category = $cat_row['pitem'];
                $is_active = ($selected_category == $category) ? true : false;
                $material_param = isset($_GET['material']) ? '&material='.urlencode($_GET['material']) : '';
                $btnStyle = $is_active ? 'background-color: #d4af37; color: #000; border-color: #d4af37;' : 'border-color: #d4af37; color: #333; background-color: white;';
                echo '<a href="?category='.urlencode($category).$material_param.'" class="btn" style="'.$btnStyle.' font-weight: 600; transition: all 0.3s ease; border-width: 1px; border-style: solid;"><i class="bi bi-flower2 me-1"></i>'.$category.'</a>';
            }
            ?>
        </div>
    </div>

    <!-- Material Buttons Section -->
    <div class="mb-5">
        <h5 class="mb-3" style="color: #333; font-weight: 600;">
            <i class="bi bi-droplet me-2" style="color: #d4af37;"></i>Select Brand/Material
        </h5>
        <div class="d-flex flex-wrap gap-2 pb-4">
            <a href="?" class="btn <?php echo (!isset($_GET['material']) ? 'active' : ''); ?>" style="<?php echo (!isset($_GET['material']) ? 'background-color: #d4af37; color: #000; border-color: #d4af37;' : 'border-color: #d4af37; color: #333; background-color: white;'); ?> font-weight: 600; transition: all 0.3s ease; border-width: 1px; border-style: solid;">
                <i class="bi bi-asterisk me-1"></i>All Brands
            </a>
            <?php
            // Get all distinct materials
            $mat_sql = "SELECT DISTINCT pcompany FROM `product` WHERE pcompany IS NOT NULL AND pcompany != '' ORDER BY pcompany";
            $mat_result = mysqli_query($con, $mat_sql);
            
            $selected_material = isset($_GET['material']) ? $_GET['material'] : '';
            
            while($mat_row = mysqli_fetch_assoc($mat_result)) {
                $material = $mat_row['pcompany'];
                $is_active = ($selected_material == $material) ? true : false;
                $category_param = isset($_GET['category']) ? '&category='.urlencode($_GET['category']) : '';
                $btnStyle = $is_active ? 'background-color: #d4af37; color: #000; border-color: #d4af37;' : 'border-color: #d4af37; color: #333; background-color: white;';
                echo '<a href="?material='.urlencode($material).$category_param.'" class="btn" style="'.$btnStyle.' font-weight: 600; transition: all 0.3s ease; border-width: 1px; border-style: solid;"><i class="bi bi-circle-fill me-1" style="font-size: 0.6rem;"></i>'.$material.'</a>';
            }
            ?>
        </div>
    </div>

    <!-- Products Display -->
    <div class="row g-4">
        <?php
        $sql = "SELECT * FROM `product` WHERE 1=1";
        $filter_display = [];
        
        // Apply category filter if selected
        if(isset($_GET['category']) && $_GET['category'] != '') {
            $category = mysqli_real_escape_string($con, $_GET['category']);
            $sql .= " AND pitem = '$category'";
            $filter_display[] = "<i class='bi bi-tag me-1'></i>Category: <strong>".$_GET['category']."</strong>";
        }
        
        // Apply material filter if selected
        if(isset($_GET['material']) && $_GET['material'] != '') {
            $material = mysqli_real_escape_string($con, $_GET['material']);
            $sql .= " AND pcompany = '$material'";
            $filter_display[] = "<i class='bi bi-droplet me-1'></i>Brand: <strong>".$_GET['material']."</strong>";
        }
        
        // Display active filters
        if(!empty($filter_display)) {
            echo '<div class="col-12 mb-3"><div class="alert alert-light border" style="border-left: 4px solid #d4af37;"><h6 class="mb-2" style="color: #d4af37; font-weight: 700;">Active Filters</h6>'.implode('<br>', $filter_display).'</div></div>';
        }
        
        $result = mysqli_query($con, $sql);
        
        if(mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $qty = $row['pqty'];
                echo '<div class="col-md-6 col-lg-3">';
                echo '<div class="card product-card h-100" style="border: none; border-radius: 10px; overflow: hidden; background: white; transition: all 0.3s ease;">';
                echo '<div style="position: relative; overflow: hidden; height: 260px;">';
                echo '<img src="../productimg/'.$row['pimg'].'" class="card-img-top" alt="'.$row['pname'].'" style="height: 100%; object-fit: cover; transition: transform 0.3s ease;">';
                echo '<div style="position: absolute; top: 10px; right: 10px; background: #d4af37; color: #000; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">₹'.$row['pprice'].'</div>';
                echo '</div>';
                echo '<div class="card-body d-flex flex-column" style="padding: 1.25rem;">';
                echo '<h6 class="card-title mb-2" style="color: #333; font-weight: 700; min-height: 40px;">'.$row['pname'].'</h6>';
                echo '<div class="mb-3">';
                echo '<p class="card-text mb-1" style="color: #999; font-size: 0.9rem;"><i class="bi bi-tag me-1" style="color: #d4af37;"></i>'.$row['pitem'].'</p>';
                echo '<p class="card-text mb-2" style="color: #999; font-size: 0.9rem;"><i class="bi bi-droplet me-1" style="color: #d4af37;"></i>'.$row['pcompany'].'</p>';
                echo '</div>';
                ?>
                <form action="purchase.php" method="post" class="mt-auto">
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 0.9rem; color: #666; font-weight: 500;">Quantity</label>
                        <input type="number" value="1" min="1" max="<?php echo $qty; ?>" name="qty" class="form-control" style="border-color: #d4af37; font-weight: 600;">
                    </div>
                    <input type="hidden" name="pid" value="<?php echo $row['pid']; ?>">
                    <input type="hidden" name="pname" value="<?php echo $row['pname']; ?>">
                    <input type="hidden" name="pprice" value="<?php echo $row['pprice']; ?>">    
                    <button type="submit" class="btn w-100" style="background-color: #d4af37; color: #000; font-weight: 600; border: none; padding: 10px; border-radius: 6px; transition: all 0.3s ease;">
                        <i class="bi bi-cart-plus me-1"></i>Buy Now
                    </button>
                </form>
                <?php
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<div class="col-12 text-center py-5"><i class="bi bi-inbox" style="font-size: 3rem; color: #ddd;"></i><p class="text-muted mt-3" style="font-size: 1.1rem;">No products found matching your filters.</p></div>';
        }
        ?>
    </div>
</div>

<style>
    .btn-outline-dark:hover {
        background-color: #d4af37 !important;
        color: #000 !important;
        border-color: #d4af37 !important;
    }

    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(212, 175, 55, 0.2) !important;
    }

    .product-card:hover img {
        transform: scale(1.05);
    }
</style>

<?php
include('footer.php');  
?>


