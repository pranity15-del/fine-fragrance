<?php
define('page','purchase');
include('header.php');
include('../admin/conn.php');

    $pid = $_POST['pid'] ?? '';
    $pname = $_POST['pname'] ?? '';
    $pprice = $_POST['pprice'] ?? '';
    $pqty = $_POST['qty'] ?? '';

    // Email stored in session
    $session_email = $_SESSION['username'] ?? '';
    $user_email = $session_email;
    // Create a friendly display name. Prefer a non-numeric contact name if present; otherwise use the email local-part
    $display_name = $session_email;

    if (!empty($session_email)) {
        $safe_email = mysqli_real_escape_string($con, $session_email);
        // Fetch contact (phone or stored name) and email
        $u_res = mysqli_query($con, "SELECT c_name, c_email FROM customer_login WHERE c_email = '$safe_email' LIMIT 1");
        if ($u_res && mysqli_num_rows($u_res) > 0) {
            $u_row = mysqli_fetch_assoc($u_res);
            if (!empty($u_row['c_email'])) $user_email = $u_row['c_email'];
            // Start with a friendly name derived from the email local-part
            $local = explode('@', $user_email)[0];
            $local = str_replace(array('.', '_', '-'), ' ', $local);
            $display_name = ucwords($local);
            // If contact contains a non-numeric name, prefer it
            if (!empty($u_row['c_name']) && !ctype_digit($u_row['c_name']) && strlen($u_row['c_name']) > 2) {
                $display_name = $u_row['c_name'];
            }
        }
    }

    // Check whether user already has a delivery address in profile
    $has_address = false;
    if (!empty($session_email)) {
        $safe_email = mysqli_real_escape_string($con, $session_email);
        $addr_res = mysqli_query($con, "SELECT address FROM customer_profile WHERE c_email = '$safe_email' LIMIT 1");
        if ($addr_res && mysqli_num_rows($addr_res) > 0) {
            $addr_row = mysqli_fetch_assoc($addr_res);
            if (!empty(trim($addr_row['address'] ?? ''))) $has_address = true;
        }
    }
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Purchase Summary</h4>
                </div>
                <div class="card-body">
                    <form action="purchase_order.php" method="post">
                        <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid); ?>">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($display_name); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user_email); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <input type="text" class="form-control" name="pname" value="<?php echo htmlspecialchars($pname); ?>" readonly>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Price</label>
                                <input type="text" class="form-control" name="pprice" value="<?php echo htmlspecialchars($pprice); ?>" readonly>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="text" class="form-control" name="pqty" value="<?php echo htmlspecialchars($pqty); ?>" readonly>
                            </div>
                        </div>
                        <hr>
                        <h4 class="text-end">Total: <strong>₹<?php echo htmlspecialchars(number_format($pprice * $pqty,2)); ?></strong></h4>
                        <?php if ($has_address): ?>
                        <button type="submit" class="btn btn-success w-100 mt-3"><i class="bi bi-check2-circle me-2"></i>Confirm Purchase</button>
                        <?php else: ?>
                        <div class="alert alert-warning">No delivery address found. Please <a href="profile.php">add your address in Profile</a> before purchasing.</div>
                        <a href="profile.php" class="btn btn-outline-primary w-100 mt-2">Add Address in Profile</a>
                        <?php endif; ?>
                        <a href="view_product.php" class="btn btn-outline-secondary w-100 mt-2">Continue Shopping</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php   

include('footer.php');
?>