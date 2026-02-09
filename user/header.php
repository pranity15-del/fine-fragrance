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
    <link rel="stylesheet" href="../css/theme.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
      :root {
        --gold: #d4af37;
        --gold-dark: #b48a18;
        --sidebar-width: 250px;
      }
      * { font-family: 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; }
      
      body { 
        background-color: #f8f9fa;
        padding-top: 70px;
      }

      /* Navbar Styles */
      .navbar {
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1030;
        background-color: #000;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }

      .navbar-brand {
        color: var(--gold) !important;
        font-weight: 700;
      }

      /* Sidebar Styles */
      .sidebar {
        position: fixed;
        left: 0;
        top: 70px;
        width: var(--sidebar-width);
        height: calc(100vh - 70px);
        background-color: #f8f9fa;
        border-right: 1px solid #dee2e6;
        overflow-y: auto;
        padding-top: 20px;
        z-index: 1020;
      }

      .sidebar-nav {
        padding: 0 15px;
      }

      .sidebar-nav .nav-link {
        color: #333;
        border-radius: 6px;
        margin-bottom: 8px;
        padding: 10px 15px;
        transition: all 0.3s ease;
      }

      .sidebar-nav .nav-link:hover {
        background-color: #e9ecef;
        color: var(--gold-dark);
      }

      .sidebar-nav .nav-link.active {
        background-color: var(--gold);
        color: #000;
        font-weight: 600;
      }

      /* Main Content Styles */
      .main-content {
        margin-left: var(--sidebar-width);
        padding: 20px;
        min-height: calc(100vh - 70px);
      }

      /* Responsive Design */
      @media (max-width: 768px) {
        :root {
          --sidebar-width: 200px;
        }
        
        .navbar-toggler {
          margin-left: auto;
        }

        .sidebar {
          transform: translateX(-100%);
          transition: transform 0.3s ease;
          width: 200px;
        }

        .sidebar.show {
          transform: translateX(0);
          box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .main-content {
          margin-left: 0;
        }
      }
    </style>

</head>  
<body>
    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg navbar-dark">
      <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
          <img src="../productimg/logo.png" alt="Fine & Fragrance" width="50" height="40" class="d-inline-block">
          Fine &amp; Fragrance
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
              <button class="nav-link dropdown-toggle text-white btn btn-link p-0" id="userMenu" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
              </button>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="myorder.php"><i class="bi bi-card-list me-2"></i>My Orders</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <!-- Navbar End -->

    <!-- Sidebar Start -->
    <div class="sidebar" id="sidebar">
      <ul class="nav flex-column sidebar-nav">
        <li class="nav-item">
          <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'index.php') !== false) ? 'active' : ''; ?>" href="index.php">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'categories.php') !== false) ? 'active' : ''; ?>" href="Categories.php">
            <i class="bi bi-grid-3x3 me-2"></i>Categories
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'profile.php') !== false) ? 'active' : ''; ?>" href="profile.php">
            <i class="bi bi-person me-2"></i>My Profile
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'myorder.php') !== false) ? 'active' : ''; ?>" href="myorder.php">
            <i class="bi bi-card-list me-2"></i>My Orders
          </a>
        </li>
      </ul>
    </div>
    <!-- Sidebar End -->

    <!-- Main Content Start -->
    <div class="main-content">
