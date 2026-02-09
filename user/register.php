<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>User Registration</h3>
                    </div>
                    <div class="card-body shadow-lg">
                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Enter Your Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            <div class="mb-3">
                            
                                <label for="c_contact" class="form-label">Enter Your contact</label>
                                <input type="text" class="form-control" id="contact" name="contact" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Enter Your Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Enter Your Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="form-check form-switch mb-3">
                              <input class="form-check-input" type="checkbox" id="addAddressToggle">
                              <label class="form-check-label" for="addAddressToggle">Add address now (optional)</label>
                            </div>

                            <div id="addressFields" style="display:none;">
                              <div class="mb-3">
                                <label class="form-label">Address (optional)</label>
                                <textarea name="address" class="form-control" rows="2"></textarea>
                              </div>
                              <div class="row g-2">
                                <div class="col-md-6">
                                  <input type="text" class="form-control" name="city" placeholder="City">
                                </div>
                                <div class="col-md-6">
                                  <input type="text" class="form-control" name="state" placeholder="State">
                                </div>
                              </div>
                              <div class="row g-2 mt-2">
                                <div class="col-md-6">
                                  <input type="text" class="form-control" name="postal_code" placeholder="Postal Code">
                                </div>
                                <div class="col-md-6">
                                  <input type="date" class="form-control" name="dob" placeholder="Date of Birth">
                                </div>
                              </div>
                            </div>

                            <div class="d-grid mt-3">
                              <button type="submit" name="register" class="btn btn-success">Register</button>
                            </div>
                        </form>

                        <script>
                          document.getElementById('addAddressToggle').addEventListener('change', function(e){
                            document.getElementById('addressFields').style.display = this.checked ? 'block' : 'none';
                          });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</body>
</html>
<?php
if(isset($_POST['register'])){
    // sanitize inputs
    include('../admin/conn.php');
    $name = mysqli_real_escape_string($con, trim($_POST['name'] ?? ''));
    $contact = mysqli_real_escape_string($con, trim($_POST['contact'] ?? ''));
    $email = mysqli_real_escape_string($con, trim($_POST['email'] ?? ''));
    $password = mysqli_real_escape_string($con, trim($_POST['password'] ?? ''));

    // basic validation
    if (empty($name) || empty($email) || empty($password)) {
        echo "<div class='alert alert-danger'>Please fill required fields</div>";
        exit;
    }

    // collect optional address fields
    $address = mysqli_real_escape_string($con, trim($_POST['address'] ?? ''));
    $city = mysqli_real_escape_string($con, trim($_POST['city'] ?? ''));
    $state = mysqli_real_escape_string($con, trim($_POST['state'] ?? ''));
    $postal_code = mysqli_real_escape_string($con, trim($_POST['postal_code'] ?? ''));
    $dob = mysqli_real_escape_string($con, trim($_POST['dob'] ?? ''));

    // check existing
    $sqlq = "SELECT * FROM customer_login WHERE c_email='$email' LIMIT 1";
    $result = mysqli_query($con, $sqlq);
    if(mysqli_num_rows($result) > 0){
        echo "<script>alert('Email already registered!'); window.location.href='register.php';</script>";
        exit;
    }

    // ensure created_at column exists on customer_login
    $col_check_created = mysqli_query($con, "SHOW COLUMNS FROM customer_login LIKE 'created_at'");
    if (!$col_check_created || mysqli_num_rows($col_check_created) === 0) {
        mysqli_query($con, "ALTER TABLE customer_login ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
    }

    // insert user
    $sql = "INSERT INTO customer_login (c_name, c_email, c_contact, c_password, created_at) VALUES ('$name', '$email', '$contact', '$password', NOW())";
    if (!mysqli_query($con, $sql)){
        echo "<div class='alert alert-danger'>Could not register: " . htmlspecialchars(mysqli_error($con)) . "</div>";
        exit;
    }

    // create profile table if not exists
    $create_profile = "CREATE TABLE IF NOT EXISTS customer_profile (
        id INT AUTO_INCREMENT PRIMARY KEY,
        c_email VARCHAR(255) NOT NULL UNIQUE,
        address TEXT NULL,
        city VARCHAR(120) NULL,
        state VARCHAR(120) NULL,
        postal_code VARCHAR(30) NULL,
        dob DATE NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    mysqli_query($con, $create_profile);

    // insert profile row only if user provided address or related fields
    $addressProvided = (!empty($address) || !empty($city) || !empty($state) || !empty($postal_code) || !empty($dob));
    if ($addressProvided) {
        $ins_profile = "INSERT INTO customer_profile (c_email, address, city, state, postal_code, dob) VALUES ('$email', '$address', '$city', '$state', '$postal_code', " . (!empty($dob) ? "'". $dob ."'" : "NULL") . ")";
        mysqli_query($con, $ins_profile);
    }

    mysqli_close($con);

    echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
    exit;
}


?>