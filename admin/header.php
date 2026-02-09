<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['is_login'])){
    header('location:login.php');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fine & Fragrance</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.bundle.min.js"></script>
    <!-- App styles -->
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/user.css">
    <link rel="stylesheet" href="../css/theme.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
      body { font-family: 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; }
      .navbar-brand { color: #d4af37 !important; font-weight:600; }
      .btn-gold { background: #d4af37; border-color: #b48a18; color: #000; }
      .navbar .nav-link { padding: 8px 12px !important; }
      .navbar .navbar-nav { gap: 0; }
    </style>
  
</head>  
<body style="margin-top: 70px; background-color: #f8f9fa;">
    <!-- navbar start -->
     <nav class="navbar navbar-expand-lg navbar-dark" style="position: fixed; top: 0; width: 100%; z-index: 1030; background-color: #000; padding: 8px 0; height: 70px; display: flex; align-items: center;">
  <div class="container-fluid" style="padding: 0 10px;">
    <a class="navbar-brand" href="index.php" style="font-size: 1rem;"><img src="../productimg/logo.png" alt="Fine & Fragrance" width="40" height="32" class="d-inline-block me-2">Fine &amp; Fragrance</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="#navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation" style="padding: 4px 8px;">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent" style="padding: 0;">
      <ul class="navbar-nav me-auto mb-0">
      
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: white; font-size: 0.9rem; padding: 8px 12px;">
            Catalog
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown" style="font-size: 0.9rem;">
            <li><a class="dropdown-item" href="add_product.php">Add Product</a></li>
            <li><a class="dropdown-item" href="view_product.php">View Product</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link " href="orders.php" id="navbarDropdown" role="button" aria-expanded="false" style="color: white; font-size: 0.9rem; padding: 8px 12px;">
          Orders
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" aria-current="page" href="index.php" style="padding: 8px 12px;"></a>
        </li>
      
        
      </ul>

      <ul class="navbar-nav ms-auto mb-0">
        <li class="nav-item dropdown">
          <button class="nav-link dropdown-toggle text-white btn btn-link p-0" id="adminUser" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: inherit; font-size: 0.9rem;">
            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminUser" style="font-size: 0.9rem;">
            <li><a class="dropdown-item" href="orders.php"><i class="bi bi-list-check me-2"></i>Orders</a></li>

            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </li>
      </ul>
   
    </div>
  </div>
</nav>
<!-- navbar End -->
<script>
document.addEventListener('DOMContentLoaded', function(){
  var btn = document.getElementById('adminUser');
  if (btn) {
    try { bootstrap.Dropdown.getOrCreateInstance(btn); } catch (e) { /* ignore */ }
  }
});
</script>
<script>
// Image hover preview (shows a larger floating preview when hovering small images)
(function(){
  if ('ontouchstart' in window) return; // skip on touch devices
  document.addEventListener('DOMContentLoaded', function(){
    var  = document.createElement('div');
    id = 'imgPreview';
    document.body.appendChild();
    var Img = document.createElement('img');
    preview.appendChild(Img);
    var active = false;
    var showTimeout = null;

    document.addEventListener('mouseover', function(e){
      var t = e.target;
      if (!t || t.tagName !== 'IMG') return;
      if (t.classList.contains('no-preview')) return;
      if (/logo/i.test(t.src)) return; // don't preview logos
      var w = t.clientWidth || t.naturalWidth || 0;
      if (w > 300) return; // only for small images
      showTimeout = setTimeout(function(){
        previewImg.src = t.src;
        preview.style.display = 'block';
        active = true;
        positionPreview(e);
      }, 180);
    });

    document.addEventListener('mousemove', function(e){
      if (!active) return;
      positionPreview(e);
    });

    document.addEventListener('mouseout', function(e){
      var t = e.target;
      if (!t || t.tagName !== 'IMG') return;
      clearTimeout(showTimeout);
      preview.style.display = 'none';
      active = false;
    });

    function positionPreview(e){
      var padding = 12;
      var vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
      var vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
      var rect = preview.getBoundingClientRect();
      var x = e.clientX + 20;
      var y = e.clientY + 20;
      if (x + rect.width + padding > vw) x = e.clientX - rect.width - 20;
      if (y + rect.height + padding > vh) y = e.clientY - rect.height - 20;
      preview.style.left = Math.max(8, x) + 'px';
      preview.style.top = Math.max(8, y) + 'px';
    }
  });
})();
</script>
<script>
// Image click preview (opens full image in Bootstrap modal)
document.addEventListener('DOMContentLoaded', function(){
  // append modal markup
  var modalHtml = '\n<div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">\n  <div class="modal-dialog modal-dialog-centered modal-lg">\n    <div class="modal-content bg-transparent border-0">\n      <div class="modal-body p-0">\n        <img src="" id="imgModalImg" class="img-fluid rounded" alt="">\n      </div>\n    </div>\n  </div>\n</div>\n';
  document.body.insertAdjacentHTML('beforeend', modalHtml);

  var imgModalEl = document.getElementById('imgModal');
  var imgModal = bootstrap.Modal.getOrCreateInstance(imgModalEl);
  var modalImg = document.getElementById('imgModalImg');

  document.addEventListener('click', function(e){
    var t = e.target;
    if (!t || t.tagName !== 'IMG') return;
    if (t.classList.contains('no-preview')) return;
    if (/logo/i.test(t.src)) return; // don't open logos
    e.preventDefault();
    modalImg.src = t.src;
    modalImg.alt = t.alt || '';
    imgModal.show();
  });

  imgModalEl.addEventListener('hidden.bs.modal', function(){
    modalImg.src = '';
  });
});
</script>
 <!-- container start  -->
  <div class="container">
    <div class="row mt-2 justify-content-center">