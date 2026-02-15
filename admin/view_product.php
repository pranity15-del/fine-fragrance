<?php
include('header.php');
include('conn.php');
?>
<div class="col-sm-8  col-md-8 col-lg-8">
    <!-- header  -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">View Products</h4>
    </div>
    <!-- header end  -->

    <?php
    if (isset($_GET['pid']) && !empty($_GET['pid'])) {
        // Show single product details
        $pid = (int)$_GET['pid'];
        $result = mysqli_query($con, "SELECT * FROM product WHERE pid='$pid'");
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            echo '<div class="card mb-4">';
            echo '  <div class="row g-0">';
            echo '    <div class="col-md-4">';
            echo '      <img src="../productimg/'.htmlspecialchars($row['pimg'] ?: 'placeholder_perfume.svg').'" class="img-fluid rounded-start" alt="'.htmlspecialchars($row['pname']).'">';
            echo '    </div>';
            echo '    <div class="col-md-8">';
            echo '      <div class="card-body">';
            echo '        <h5 class="card-title">'.htmlspecialchars($row['pname']).'</h5>';
            echo '        <p class="card-text"><strong>Brand:</strong> '.htmlspecialchars($row['brand']).'</p>';
            echo '        <p class="card-text"><strong>Category:</strong> '.htmlspecialchars($row['pitem']).'</p>';
            echo '        <p class="card-text"><strong>Material:</strong> '.htmlspecialchars($row['pcompany']).'</p>';
            echo '        <p class="card-text"><strong>Size:</strong> '.htmlspecialchars($row['size_ml']).' ml</p>';
            echo '        <p class="card-text"><strong>Concentration:</strong> '.htmlspecialchars($row['concentration']).'</p>';
            echo '        <p class="card-text"><strong>SKU:</strong> '.htmlspecialchars($row['sku']).'</p>';
            echo '        <p class="card-text"><strong>Scent Notes:</strong> '.htmlspecialchars($row['scent_notes']).'</p>';
            echo '        <p class="card-text"><strong>Stock:</strong> '.htmlspecialchars($row['stock']).'</p>';
            echo '        <p class="card-text"><strong>Price:</strong> ₹'.number_format($row['pprice'],2).'</p>';
            echo '        <p class="card-text"><strong>Description:</strong> '.htmlspecialchars($row['pdis']).'</p>';
            echo '      </div>';
            echo '    </div>';
            echo '  </div>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-warning">Product not found.</div>';
        }
    } else {
        // Show all products (table/list)
        ?>
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

        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
              <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Brand</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
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
                echo '<tr>';
                echo '<td><img src="../productimg/'. $pimg .'" alt="'. $pname .'" style="width:60px;height:60px;object-fit:cover;border-radius:6px;"></td>';
                echo '<td>'. $pname .'</td>';
                echo '<td>'. $brand .'</td>';
                echo '<td>₹'. $price .'</td>';
                echo '<td>'. $stock .'</td>';
                echo '<td class="d-flex gap-2">';
                echo '<form method="post" action="update.php" class="m-0"><input type="hidden" name="uid" value="'. $pid .'"><button class="btn btn-outline-warning btn-sm"><i class="bi bi-pencil"></i> Edit</button></form>';
                echo '<form method="post" action="delete.php" class="m-0" onsubmit="return confirm(\'Delete this product?\');"><input type="hidden" name="did" value="'. $pid .'"><button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</button></form>';
                echo '</td>';
                echo '</tr>';
              }
            } else {
              echo '<tr><td colspan="6" class="text-center">No products found. <a href="add_product.php">Add a product</a></td></tr>';
            }
            ?>
            </tbody>
          </table>
        </div>
    <?php } ?>
</div>

<?php
include('footer.php');  
?>