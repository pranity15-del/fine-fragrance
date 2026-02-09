<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin login</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Poppins',system-ui,-apple-system,'Segoe UI',Roboto,Arial}</style>
</head>
<body>
<div class="container">
  <div class="row justify-content-center">
    <div class="col-sm-6">
      <div class="card mt-5 shadow-sm">
        <div class="card-body">
          <h3 class="card-title text-center mb-3"><i class="bi bi-shield-lock me-2"></i>Admin Login</h3>
          <form action="login.php" method="post">
            <div class="mb-3">
              <label class="form-label">UserName</label>
              <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" class="form-control" name="passward" required>
            </div>
            <button type="submit" class="btn btn-success w-100" name="submit"><i class="bi bi-box-arrow-in-right me-1"></i>Sign in</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
include('conn.php');
if(isset($_POST['submit'])){
$username=$_POST['username'];
$passward=$_POST['passward'];
$sqlquery="SELECT * FROM user_login WHERE username='$username' AND passward='$passward'";
$result=mysqli_query($con,$sqlquery);
if(mysqli_num_rows($result)>0){
  $_SESSION['is_login']=true;
  $_SESSION['uname']=$username;
  header('location:index.php');
  exit;
}else{
echo "Invalid credentials";
}
}
?>
</body>
</html>