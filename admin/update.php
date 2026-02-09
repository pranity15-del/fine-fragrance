<?php
include('header.php');
include('conn.php');
$uid=$_POST['uid'];
$sqlq="select * from product where pid='$uid'";
$result=mysqli_query($con,$sqlq);
while($row=mysqli_fetch_assoc($result)){
?>
   <div class="col-sm-6 col-md-6 col-lg-6">
            <!-- header  -->
            
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h3 class="mb-0">Update Product</h3>
                  <a href="view_product.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>View Products</a>
                </div>
             
            <!-- header end  -->
             <form action="update_product.php" method="post" class="shadow-lg p-4" enctype="multipart/form-data">
                
             <div class="mb-2">
                    <label for="product_name" class="form-label">Product Name</label>
                    <input type="text" class="form-control"  name="pname" value="<?php echo $row['pname'];?>" required>
                    <input type="hidden" name="update_id" value="<?php echo $row['pid'];?>">
                </div>
                <div class="mb-2">
                    <label for="product_item" class="form-label">Product Category</label>
                    <input type="text" class="form-control"  name="pitem"value="<?php echo $row['pitem'];?>" required>
                </div>
                <div class="mb-2">
                    <label for="product_company" class="form-label">Product Material</label>
                    <input type="text" class="form-control"  name="pcompany"value="<?php echo htmlspecialchars($row['pcompany']);?>" required>
                </div>
                <div class="mb-2">
                    <label for="brand" class="form-label">Brand</label>
                    <input type="text" class="form-control"  name="brand" value="<?php echo htmlspecialchars($row['brand'] ?? ''); ?>" required>
                </div>
                <div class="row">
                  <div class="col-md-4 mb-2">
                    <label for="size_ml" class="form-label">Size (ml)</label>
                    <input type="number" class="form-control"  name="size_ml" value="<?php echo htmlspecialchars($row['size_ml'] ?? 50);?>" required>
                  </div>
                  <div class="col-md-4 mb-2">
                    <label for="concentration" class="form-label">Concentration</label>
                    <select name="concentration" class="form-control">
                      <option <?php echo (isset($row['concentration']) && $row['concentration']=='EDP') ? 'selected' : '';?>>EDP</option>
                      <option <?php echo (isset($row['concentration']) && $row['concentration']=='EDT') ? 'selected' : '';?>>EDT</option>
                      <option <?php echo (isset($row['concentration']) && $row['concentration']=='Parfum') ? 'selected' : '';?>>Parfum</option>
                      <option <?php echo (isset($row['concentration']) && $row['concentration']=='Eau de Cologne') ? 'selected' : '';?>>Eau de Cologne</option>
                    </select>
                  </div>
                  <div class="col-md-4 mb-2">
                    <label for="sku" class="form-label">SKU</label>
                    <input type="text" class="form-control" name="sku" value="<?php echo htmlspecialchars($row['sku'] ?? ''); ?>">
                  </div>
                </div>
                <div class="mb-2">
                    <label for="scent_notes" class="form-label">Scent Notes</label>
                    <textarea class="form-control" id="scent_notes" name="scent_notes" rows="2"><?php echo htmlspecialchars($row['scent_notes'] ?? ''); ?></textarea>
                </div>
                <div class="mb-2">
                    <label for="stock" class="form-label">Stock</label>
                    <input type="number" class="form-control"  name="stock" value="<?php echo htmlspecialchars($row['stock'] ?? $row['pqty']);?>" required>
                </div>
                <div class="mb-2">
                    <label for="product_price" class="form-label">Product Price</label>
                    <input type="text" class="form-control"  name="pprice" value="<?php echo $row['pprice'];?>" required>
                </div>
                <div class="mb-2">
                    <label for="product_qty" class="form-label">Product Qty</label>
                    <input type="text" class="form-control"  name="pqty" value="<?php echo $row['pqty'];?>" required>
                </div> 
                <div class="mb-2">
                    <label for="product_amount" class="form-label">Product Amount</label>
                    <input type="text" class="form-control"  name="pamount"value="<?php echo $row['pamount'];?>" required>
                </div>
                <div class="mb-2">
                    <label for="product_description" class="form-label">Product Description</label>
                    <input type="text" class="form-control"  value="<?php echo $row['pdis'];?>" name="product_description" required>
                </div>
                <?php if (!empty($row['pimg'])): ?>
                <div class="mb-2">
                    <label class="form-label">Current Image</label>
                    <div><img src="../productimg/<?php echo htmlspecialchars($row['pimg']); ?>" style="height:80px"></div>
                </div>
                <?php endif; ?>
                <div class="mb-3">
                    <label for="formFile" class="form-label">Upload Product Image (leave empty to keep current)</label>
                    <input class="form-control" type="file" id="formFile" name="pimg">
                </div>
                <button type="submit" class="btn btn-warning" name="add_product">Update Product</button>
             
                
             </div> 
            </form>
            
        </div>
    </div>
</div>



<?php
 
}
?>



