<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fine & Fragrance</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="js/bootstrap.min.js"></script>
    <style>body{font-family:'Poppins',system-ui,-apple-system,'Segoe UI',Roboto,Arial}</style>
</head>
<body>
   <!-- navbar start -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="position: fixed; top: 0; width: 100%; z-index: 1030; background-color: #000;">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php"><img src="productimg/logo.png" alt="Fine & Fragrance" width="50" height="40" class="d-inline-block">Fine &amp; Fragrance</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-center" id="navbarSupportedContent">
      <ul class="navbar-nav mb-2 mb-lg-0">
        <li class="nav-item mx-2">
          <a class="nav-link" href="user/login.php" style="color: white;">User Login</a>
        </li>
        <li class="nav-item mx-2">
          <a class="nav-link" href="admin/login.php" style="color: white;">Admin Login</a>
        </li>
      </ul>
    </div> 
  </div>
</nav>
<!-- navbar End -->
<div style="margin-top:70px;"></div>
<!-- #####################################################/ -->

    <!--slider start-->
    <div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel">
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1" aria-label="Slide 2"></button>
    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2" aria-label="Slide 3"></button>
  </div>
  <div class="carousel-inner">
    <div class="carousel-item active position-relative c1">
      <img src="productimg/c1.png" class="d-block w-100" alt="..." style="height:70vh; object-fit:cover;">
      <div class="carousel-overlay position-absolute top-0 start-0 w-100 h-100"></div>
      <div class="carousel-caption d-none d-md-block text-start" style="left:5%; right:auto; bottom:30%;">
        <h1 class="display-5 fw-bold text-white">Discover Signature Fragrances</h1>
        <p class="lead text-white-50">Fine &amp; Fragrance — curated perfumes for every mood. Free shipping over ₹1999.</p>
        <a href="user/view_product.php" class="btn btn-gold btn-lg"><i class="bi bi-bag-plus me-2"></i>Shop Now</a>
      </div>
    </div>
    <div class="carousel-item position-relative c2">
      <img src="productimg/c2.png" class="d-block w-100" alt="..." style="height:70vh; object-fit:cover;">
      <div class="carousel-overlay position-absolute top-0 start-0 w-100 h-100"></div>
      <div class="carousel-caption d-none d-md-block text-start" style="left:5%; right:auto; bottom:30%;">
        <h1 class="display-5 fw-bold text-white">New Arrivals — Limited Edition</h1>
        <p class="lead text-white-50">Limited release blends and exclusive gift sets.</p>
        <a href="user/view_product.php" class="btn btn-gold btn-lg"><i class="bi bi-bag-plus me-2"></i>Explore Collection</a>
      </div>
    </div>
    <div class="carousel-item position-relative c3">
      <img src="productimg/c3.png" class="d-block w-100" alt="..." style="height:70vh; object-fit:cover;">
      <div class="carousel-overlay position-absolute top-0 start-0 w-100 h-100"></div>
      <div class="carousel-caption d-none d-md-block text-start" style="left:5%; right:auto; bottom:30%;">
        <h1 class="display-5 fw-bold text-white">Gifts & Sets</h1>
        <p class="lead text-white-50">Perfectly packaged gifts for special moments.</p>
        <a href="user/view_product.php" class="btn btn-dark btn-lg"><i class="bi bi-bag-plus me-2"></i>Browse</a>
      </div>
    </div>
  </div> 
  <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>
    <!--slider end --> 

<?php
include 'admin/conn.php';
$feat = mysqli_query($con, "SELECT * FROM product ORDER BY pid DESC LIMIT 6");
?>

<section class="featured bg-dark text-white py-5">
 <div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Featured Collection</h3>
    <a href="user/view_product.php" class="btn btn-gold">Shop All</a>
  </div>
  <div class="row g-4">
    <?php
      if(mysqli_num_rows($feat)>0){
        while($f = mysqli_fetch_assoc($feat)){
          $img = $f['pimg'] ? 'productimg/'.htmlspecialchars($f['pimg']) : 'productimg/placeholder_perfume.svg';
          echo '<div class="col-md-4">';
          echo '<div class="card bg-transparent border-0 text-center featured-card">';
          echo '<img src="'. $img .'" class="img-fluid rounded mb-3" style="height:220px; object-fit:cover;">';
          echo '<h5 class="text-white">'.htmlspecialchars($f['pname']).'</h5>';
          echo '<p class="muted-small text-white-50">₹'.number_format($f['pprice'],2).'</p>';
          echo '<a href="user/product.php?pid='. $f['pid'] .'" class="btn btn-outline-light btn-sm">View</a>';
          echo '</div></div>';
        }
      } else {
        echo '<div class="col-12 text-center text-white-50">No featured products yet.</div>';
      }
    ?>
  </div>
 </div>
</section>

<div class="container py-5">
  <h3 class="text-center mb-4">Explore Categories</h3>
  <div class="row g-4">
    <div class="col-md-4 c1">
      <div class="card p-3 text-center">
        <img src="productimg/c1.png" alt="Fragrances" class="img-fluid rounded mb-3" style="height:160px;object-fit:cover;">
        <h5>Fragrances</h5>
        <p class="muted-small">Signature perfumes and classic blends.</p>
        <a href="user/view_product.php" class="btn btn-outline-dark btn-sm mt-2">Shop Fragrances</a>
      </div>
    </div>
    <div class="col-md-4 c2">
      <div class="card p-3 text-center">
        <img src="productimg/c2.png" alt="Gift Sets" class="img-fluid rounded mb-3" style="height:160px;object-fit:cover;">
        <h5>Gift Sets</h5>
        <p class="muted-small">Curated gift sets for special occasions.</p>
        <a href="user/view_product.php" class="btn btn-outline-dark btn-sm mt-2">Explore Gift Sets</a>
      </div>
    </div>
    <div class="col-md-4 c3">
      <div class="card p-3 text-center">
        <img src="productimg/c3.png" alt="New Arrivals" class="img-fluid rounded mb-3" style="height:160px;object-fit:cover;">
        <h5>New Arrivals</h5>
        <p class="muted-small">Latest fragrances and limited editions.</p>
        <a href="user/view_product.php" class="btn btn-outline-dark btn-sm mt-2">Browse New Arrivals</a>
      </div>
    </div> 
  </div>
</div>

<footer class="footer bg-dark text-white mt-5">
  <div class="container py-4 text-center">
    <div class="row">
      <div class="col-md-6 mb-3 mb-md-0">
        <h5 class="mb-1">Fine &amp; Fragrance</h5>
        <small class="muted-small">Curated perfumes & gifts — Fine &amp; Fragrance</small>
      </div>
      <div class="col-md-6">
        <p class="mb-0">Follow us:
          <a href="https://www.instagram.com/fineandfragrance" target="_blank" rel="noopener noreferrer" class="text-white me-2" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="mailto:contact@fineandfragrance.com" class="text-white me-2" aria-label="Gmail"><i class="bi bi-envelope-fill"></i></a>
          <a href="https://wa.me/919076484862" rel="noopener noreferrer" class="text-white" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
        </p>
      </div>
    </div>
  </div>
</footer>

</body>
</html> 