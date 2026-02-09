<?php
include('header.php');
?>
<div class="col-sm-8  col-md-8 col-lg-8">
    <!-- header  -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">View Products</h4>
      <a href="add_product.php" class="btn btn-success btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Product</a>
    </div>
    <!-- header end  -->
    <!-- Premium catalog controls -->
    <form method="get" class="row g-2 mb-4 align-items-center">
      <div class="col-md-4">
        <input class="form-control" name="q" placeholder="Search by product name or SKU" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
      </div>
      <div class="col-md-3">
        <select name="brand" class="form-select">
          <option value="">All Brands</option>
          <?php
            $bq = mysqli_query($con, "SELECT DISTINCT brand FROM product WHERE brand IS NOT NULL AND brand!='' ORDER BY brand");
            while($br = mysqli_fetch_assoc($bq)){
                $sel = (isset($_GET['brand']) && $_GET['brand']==$br['brand']) ? 'selected' : '';
                echo '<option value="'.htmlspecialchars($br['brand']).'" '.$sel.'>'.htmlspecialchars($br['brand']).'</option>';
            }
          ?>
        </select>
      </div>
      <div class="col-md-2">
        <button class="btn btn-gold w-100">Apply</button>
      </div>
      <div class="col-md-3 text-end">
        <a href="add_product.php" class="btn btn-outline-light btn-gold">+ Add Product</a>
      </div>
    </form>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4 admin-product-grid">
      <?php
        // Build query with filters
        $conds = [];
        if(!empty($_GET['q'])){
          $q = mysqli_real_escape_string($con, $_GET['q']);
          $conds[] = "(pname LIKE '%$q%' OR sku LIKE '%$q%')";
        }
        if(!empty($_GET['brand'])){
          $b = mysqli_real_escape_string($con, $_GET['brand']);
          $conds[] = "brand='$b'";
        }
        $where = '';
        if(count($conds)>0) $where = 'WHERE '.implode(' AND ', $conds);
        $sqlq = "SELECT * FROM product $where ORDER BY pid DESC";
        $result = mysqli_query($con, $sqlq);
        if(mysqli_num_rows($result)>0){
          while($row = mysqli_fetch_assoc($result)){
            $pid = (int)$row['pid'];
            $pimg = htmlspecialchars($row['pimg'] ?: 'placeholder_perfume.svg');
            $pname = htmlspecialchars($row['pname']);
            $brand = htmlspecialchars($row['brand'] ?? '');
            $price = number_format($row['pprice'],2);
            $stock = htmlspecialchars($row['stock'] ?? $row['pqty']);
            $desc = htmlspecialchars($row['scent_notes'] ?? $row['pdis']);
            echo '<div class="col">';
            echo '  <div class="card product-card h-100 shadow-sm">';
            echo '    <img src="../productimg/'. $pimg .'" class="card-img-top" alt="'. $pname .'">';
            echo '    <div class="card-body">';
            echo '      <h5 class="card-title">'. $pname .'</h5>';
            echo '      <p class="small text-muted mb-1">'. $brand .' • '.htmlspecialchars($row['concentration'] ?? '') .' • '.htmlspecialchars($row['size_ml'] ?? '') .'ml</p>';
            echo '      <p class="mb-2"><strong>₹'. $price .'</strong> <span class="text-muted small"> | Stock: '. $stock .'</span></p>';
            echo '      <p class="small text-muted">'. (strlen($desc)>140 ? substr($desc,0,137).'...' : $desc) .'</p>';
            echo '    </div>';
            echo '    <div class="card-footer bg-white d-flex gap-2">';
            echo '      <form method="post" action="update.php" class="m-0"><input type="hidden" name="uid" value="'. $pid .'"><button class="btn btn-outline-warning btn-sm"><i class="bi bi-pencil"></i> Edit</button></form>';
            echo '      <form method="post" action="delete.php" class="m-0" onsubmit="return confirm(\'Delete this product?\');"><input type="hidden" name="did" value="'. $pid .'"><button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</button></form>';
            echo '      <button class="btn btn-outline-secondary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#imagesModal'. $pid .'"><i class="bi bi-images"></i> Images</button>';
            echo '    </div>';
            echo '  </div>';
            echo '</div>';

            // images modal
            echo '<div class="modal fade" id="imagesModal'. $pid .'" tabindex="-1" aria-hidden="true">';
            echo '  <div class="modal-dialog modal-dialog-centered modal-lg">';
            echo '    <div class="modal-content">';
            echo '      <div class="modal-header">';
            echo '        <h5 class="modal-title">Images — '. $pname .'</h5>';
            echo '        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
            echo '      </div>';
            echo '      <div class="modal-body">';
            echo '        <div class="row g-2">';
            $imgs = mysqli_query($con, "SELECT filename FROM product_images WHERE product_id=$pid ORDER BY is_primary DESC, id ASC");
            while($img = mysqli_fetch_assoc($imgs)){
              echo '<div class="col-4"><img src="../productimg/'.htmlspecialchars($img['filename']).'" class="img-fluid rounded" alt=""></div>';
            }
            echo '        </div>';
            echo '      </div>';
            echo '    </div>';
            echo '  </div>';
            echo '</div>';
          }
        } else {
          echo '<div class="col-12 text-center py-5"><p class="text-muted">No products found. <a href="add_product.php">Add a product</a></p></div>';
        }
      ?>
    </div>

</div>

<?php
include('footer.php');  
?>