<?php
    include('../admin/conn.php');
    $pid = mysqli_real_escape_string($con, $_POST['pid'] ?? '');
    $pname = mysqli_real_escape_string($con, $_POST['pname'] ?? '');
    $pprice = mysqli_real_escape_string($con, $_POST['pprice'] ?? '');
    $qty = mysqli_real_escape_string($con, $_POST['pqty'] ?? '');
    // Accept either `username` (from form) or legacy `user` field
    $user = mysqli_real_escape_string($con, $_POST['username'] ?? $_POST['user'] ?? '');
    // Use provided name if available, otherwise fall back to username
    $name = mysqli_real_escape_string($con, $_POST['name'] ?? $user);

    // Check user has an address in profile before inserting purchase
    $addr_res = mysqli_query($con, "SELECT address FROM customer_profile WHERE c_email = '$user' LIMIT 1");
    $has_address = false;
    if ($addr_res && mysqli_num_rows($addr_res) > 0) {
        $addr_row = mysqli_fetch_assoc($addr_res);
        if (!empty(trim($addr_row['address'] ?? ''))) $has_address = true;
    }
    if (!$has_address) {
        echo '<script>alert("Please add your address in your Profile page before purchasing.");window.location.href="profile.php";</script>';
        exit;
    }

    // Insert purchase with status and date so it appears in admin orders
    $sql = "INSERT INTO `purchase` (`pname`, `user`, `name`, `pprice`, `pqty`, `prod_id`, `status`, `pdate`) VALUES ('$pname', '$user', '$name', '$pprice', '$qty', '$pid', 'pending', NOW())";
    if(mysqli_query($con, $sql)){
        $order_id = mysqli_insert_id($con);
        // Also insert into myorder table (escape values above)
        $myorder_sql = "INSERT INTO `myorder` (`order_id`, `pname`, `user`, `name`, `pprice`, `pqty`, `prod_id`, `status`, `pdate`, `created_at`) VALUES ('$order_id', '$pname', '$user', '$name', '$pprice', '$qty', '$pid', 'pending', NOW(), NOW())";
        mysqli_query($con, $myorder_sql);
        echo '<script>alert("✓ Purchase Successful!\\n\\nOrder ID: '.$order_id.'\\n\\nRedirecting to home page...");window.location.href="index.php";</script>'; 
    }else{
        echo '<script>alert("Purchase Failed: '.mysqli_error($con).'" );window.location.href="index.php";</script>'; 
    }
        
?>
