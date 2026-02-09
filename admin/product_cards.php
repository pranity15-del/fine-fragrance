<?php
include('header.php');  
?>

<?php
include('conn.php');
$sql="SELECT * FROM `product`";
$result=mysqli_query($con,$sql);
while($row=mysqli_fetch_assoc($result)){
    echo '<div class="col-md-3 mb-4">';
    echo '<div class="card product-card">';
    echo '<img src="../productimg/'.$row['pimg'].'" class="card-img-top" alt="Product Image" style="height: 300px; object-fit: cover;">';
    echo '<div class="card-body">';
    echo '<h5 class="card-title">'.htmlspecialchars($row['pname']).'</h5>';
    echo '<p class="muted-small mb-2">'.htmlspecialchars($row['pitem']).' • '.htmlspecialchars($row['pcompany']).'</p>';
    echo '<p class="card-text"><strong>₹'.htmlspecialchars(number_format($row['pprice'],2)).'</strong></p>';
    echo '<p class="card-text">Qty: '.htmlspecialchars($row['pqty']).'</p>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
?>
<?php
include('footer.php');  
?>