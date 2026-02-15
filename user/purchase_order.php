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
    $payment_method = mysqli_real_escape_string($con, $_POST['payment_method'] ?? 'Simulated Payment');

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

    // Simulate payment if selected
    if ($payment_method === 'Simulated Payment') {
        // Simulate a random payment outcome (success/failure)
        $success = rand(0, 1) ? true : false;
        if (!$success) {
            echo '<script>alert("Simulated Payment Failed! Please try again or choose Cash on Delivery.");window.location.href="purchase.php?pid='.$pid.'";</script>';
            exit;
        }
    }
    $status = ($payment_method === 'Cash on Delivery') ? 'pending (COD)' : 'pending';
    // Insert purchase with payment method
    $sql = "INSERT INTO `purchase` (`pname`, `user`, `name`, `pprice`, `pqty`, `prod_id`, `status`, `pdate`, `payment_method`) VALUES ('$pname', '$user', '$name', '$pprice', '$qty', '$pid', '$status', NOW(), '$payment_method')";
    if(mysqli_query($con, $sql)){
        $order_id = mysqli_insert_id($con);
        // Also insert into myorder table (escape values above)
        $myorder_sql = "INSERT INTO `myorder` (`order_id`, `pname`, `user`, `name`, `pprice`, `pqty`, `prod_id`, `status`, `pdate`, `created_at`, `payment_method`) VALUES ('$order_id', '$pname', '$user', '$name', '$pprice', '$qty', '$pid', '$status', NOW(), NOW(), '$payment_method')";
        mysqli_query($con, $myorder_sql);
        $msg = ($payment_method === 'Cash on Delivery') ? 'Order placed with Cash on Delivery!\\n\\nOrder ID: '.$order_id : '✓ Purchase Successful!\\n\\nOrder ID: '.$order_id;
        echo '<script>alert("'.$msg.'\\n\\nRedirecting to home page...");window.location.href="index.php";</script>'; 
    }else{
        echo '<script>alert("Purchase Failed: '.mysqli_error($con).'" );window.location.href="index.php";</script>'; 
    }
        
?>
