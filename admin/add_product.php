<?php
include('header.php');  
?>


        <div class="col-sm-6 col-md-6 col-lg-6">
            <!-- header  -->
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h3 class="mb-0">Add Product</h3>
              <a href="view_product.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>View Products</a>
            </div>
            <!-- header end  -->
            <?php if(!empty(
                $success ?? '' )){ echo '<div class="alert alert-success">'.htmlspecialchars($success).'</div>'; } ?>
            <?php if(!empty(
                $error ?? '' )){ echo '<div class="alert alert-danger">'.htmlspecialchars($error).'</div>'; } ?>
             <form action="add_product.php" method="post" class="shadow-lg p-4" enctype="multipart/form-data">
                
             <div class="mb-2">
                    <label for="product_name" class="form-label">Product Name</label>
                    <input type="text" class="form-control"  name="pname" required>
                </div>
                <div class="mb-2">
                    <label for="product_category" class="form-label">Product category</label>
                    <input type="text" class="form-control"  name="pitem" required>
                </div>
                <div class="mb-2">
                    <label for="product_company" class="form-label">Product Material</label>
                    <input type="text" class="form-control"  name="pcompany" required>
                </div>
                <div class="mb-2">
                    <label for="brand" class="form-label">Brand</label>
                    <input type="text" class="form-control"  name="brand" required>
                </div>
                <div class="row">
                  <div class="col-md-4 mb-2">
                    <label for="size_ml" class="form-label">Size (ml)</label>
                    <input type="number" class="form-control"  name="size_ml" value="50" required>
                  </div>
                  <div class="col-md-4 mb-2">
                    <label for="concentration" class="form-label">Concentration</label>
                    <select name="concentration" class="form-control">
                      <option>EDP</option>
                      <option>EDT</option>
                      <option>Parfum</option>
                      <option>Eau de Cologne</option>
                    </select>
                  </div>
                  <div class="col-md-4 mb-2">
                    <label for="sku" class="form-label">SKU</label>
                    <input type="text" class="form-control" name="sku">
                  </div>
                </div>
                <div class="mb-2">
                    <label for="scent_notes" class="form-label">Scent Notes</label>
                    <textarea class="form-control" id="scent_notes" name="scent_notes" rows="2"></textarea>
                </div>
                <div class="mb-2">
                    <label for="stock" class="form-label">Stock</label>
                    <input type="number" class="form-control"  name="stock" value="0" required>
                </div>
                <div class="mb-2">
                    <label for="product_price" class="form-label">Product Price</label>
                    <input type="text" class="form-control"  name="pprice" required>
                </div>
                <div class="mb-2">
                    <label for="product_qty" class="form-label">Product Qty</label>
                    <input type="text" class="form-control"  name="pqty" required>
                </div>
                <div class="mb-2">
                    <label for="product_amount" class="form-label">Product Amount</label>
                    <input type="text" class="form-control"  name="pamount" required>
                </div>
                <div class="mb-2">
                    <label for="product_description" class="form-label">Product Description</label>
                    <textarea class="form-control" id="product_description" name="product_description" rows="3" required></textarea>
                </div> 
                <div class="mb-3">
                    <label for="formFile" class="form-label">Upload Product Image</label>
                    <input class="form-control" type="file" id="formFile" name="pimg">
                </div>
                <button type="submit" class="btn btn-success" name="add_product"><i class="bi bi-plus-lg me-1"></i> Add Product</button>
             
                
             </div> 
            </form>
            
        </div>
    </div>
</div>
<?php
if(isset($_POST['add_product'])){
    include('conn.php');
    // sanitize inputs
    $pname = mysqli_real_escape_string($con, $_POST['pname'] ?? '');
    $pitem = mysqli_real_escape_string($con, $_POST['pitem'] ?? '');
    $pcompany = mysqli_real_escape_string($con, $_POST['pcompany'] ?? '');
    $brand = mysqli_real_escape_string($con, $_POST['brand'] ?? '');
    $size_ml = (int)($_POST['size_ml'] ?? 50);
    $concentration = mysqli_real_escape_string($con, $_POST['concentration'] ?? 'EDP');
    $sku = mysqli_real_escape_string($con, $_POST['sku'] ?? '');
    $scent_notes = mysqli_real_escape_string($con, $_POST['scent_notes'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    $pprice = mysqli_real_escape_string($con, $_POST['pprice'] ?? '0');
    $pqty = (int)($_POST['pqty'] ?? $stock);
    $pamount = mysqli_real_escape_string($con, $_POST['pamount'] ?? '0');
    $pdescription = mysqli_real_escape_string($con, $_POST['product_description'] ?? '');

    $filename = basename($_FILES["pimg"]["name"] ?? '');
    $target_dir = "../productimg/";
    $target_file = $target_dir . $filename;
    if (!empty($filename) && move_uploaded_file($_FILES["pimg"]["tmp_name"], $target_file)){
       $sqlq = "INSERT INTO `product` (`pname`,`brand`,`pitem`,`pcompany`,`size_ml`,`concentration`,`sku`,`scent_notes`,`stock`,`pqty`,`pprice`,`pamount`,`pdis`,`pimg`) VALUES ('$pname','$brand','$pitem','$pcompany','$size_ml','$concentration','$sku','$scent_notes','$stock','$pqty','$pprice','$pamount','$pdescription','$filename')";
    } else {
       // Insert without image
       $sqlq = "INSERT INTO `product` (`pname`,`brand`,`pitem`,`pcompany`,`size_ml`,`concentration`,`sku`,`scent_notes`,`stock`,`pqty`,`pprice`,`pamount`,`pdis`,`pimg`) VALUES ('$pname','$brand','$pitem','$pcompany','$size_ml','$concentration','$sku','$scent_notes','$stock','$pqty','$pprice','$pamount','$pdescription','placeholder_perfume.svg')";
    }
    $result = mysqli_query($con, $sqlq);
    if($result){
        $success = 'Product Added Successfully';
    }else{
        $error = 'Product Not Added: ' . mysqli_error($con);
    }
    
}



?>

<?php
include('footer.php');  
?>