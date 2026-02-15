<?php
define('page','myorder');
include('header.php');
include('../admin/conn.php');
// Ensure session username exists; redirect if not
$username = $_SESSION['username'] ?? null;
if (empty($username)) {
    header('Location: login.php');
    exit;
}
$username_safe = mysqli_real_escape_string($con, $username);
?>
<div class="container" style="margin-top:100px;">
  <h2 class="mb-3">My Orders</h2>
  <?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars(urldecode($_GET['msg'])); ?></div>
  <?php endif; ?>
  <ul class="nav nav-tabs mb-3" id="ordersTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">Pending Orders</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">Order History</button>
    </li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="pending" role="tabpanel">
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-dark">
            <tr>
              <th>Order ID</th>
              <th>Product</th>
              <th>Name</th>
              <th>Email</th>
              <th>Qty</th>
              <th>Price</th>
              <th>Payment</th>
              <th>Order Date</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sql = "SELECT * FROM myorder WHERE (`user`='$username_safe' OR `name`='$username_safe') AND (status NOT IN ('delivered','cancelled') OR status IS NULL) ORDER BY order_id DESC";
            $res = mysqli_query($con, $sql);
            if ($res && mysqli_num_rows($res) > 0) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $order_id = isset($row['order_id']) ? $row['order_id'] : (isset($row['pid']) ? $row['pid'] : 'N/A');
                    $status = strtolower($row['status'] ?? '');
                    // Status badge styling
                    $badge_color = 'secondary';
                    if($status == 'pending' || $status == 'order placed') {
                        $badge_color = 'warning';
                    } elseif($status == 'processing' || $status == 'confirmed') {
                        $badge_color = 'info';
                    } elseif($status == 'shipped') {
                        $badge_color = 'primary';
                    } elseif($status == 'delivered' || $status == 'completed') {
                        $badge_color = 'success';
                    } elseif($status == 'cancelled' || $status == 'rejected') {
                        $badge_color = 'danger';
                    }
                    $display_qty = intval($row['pqty'] ?? 0);
                    if ($display_qty < 1) $display_qty = 1;
                    $total = $row['pprice'] * $display_qty;
                    echo '<tr>';
                    echo '<td><strong>#'.htmlspecialchars($order_id).'</strong></td>';
                    echo '<td>'.htmlspecialchars($row['pname'] ?? 'N/A').'</td>';
                    echo '<td>'.htmlspecialchars($row['name'] ?? 'N/A').'</td>';

                    echo '<td>'.htmlspecialchars($row['user'] ?? 'N/A').'</td>';
                    echo '<td>'.htmlspecialchars($display_qty).'</td>';
                    echo '<td>₹'.htmlspecialchars($row['pprice']).'</td>';
                    echo '<td>'.htmlspecialchars($row['payment_method'] ?? 'N/A').'</td>';

                    echo '<td>'.htmlspecialchars($row['pdate'] ?? $row['created_at'] ?? '').'</td>';

                    echo '<td><span class="badge bg-'.$badge_color.'">'.htmlspecialchars($row['status']).'</span></td>';

                    // Action (cancel) - only for normal orders (no customization_id) and not delivered/cancelled
                    $can_cancel = empty($row['customization_id']) && !in_array(strtolower($row['status'] ?? ''), ['delivered','cancelled']);
                    echo '<td>';
                    if ($can_cancel) {
                        echo '<form action="cancel_order.php" method="post" style="display:inline-block" onsubmit="return confirm(\'Are you sure you want to cancel this order?\')">';
                        echo '<input type="hidden" name="order_id" value="'.htmlspecialchars($order_id).'">';
                        echo '<button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i>Cancel</button>';
                        echo '</form>';
                    } else {
                        echo '&mdash;';
                    }
                    echo '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="9" class="text-center">No pending orders</td></tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="tab-pane fade" id="history" role="tabpanel">
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-dark">
            <tr>
              <th>Order ID</th>
              <th>Product</th>
              <th>User Name</th>
              <th>Email</th>
              <th>Qty</th>
              <th>Price</th>
              <th>Total Price</th>
              <th>Payment</th>
              <th>Order Date</th>
              <th>Delivered Date</th>
              <th>Cancelled Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sql2 = "SELECT * FROM myorder WHERE (`user`='$username_safe' OR `name`='$username_safe') AND status IN ('delivered','cancelled') ORDER BY order_id DESC";
            $res2 = mysqli_query($con, $sql2);
            if ($res2 && mysqli_num_rows($res2) > 0) {
                while ($row = mysqli_fetch_assoc($res2)) {
                    $order_id = isset($row['order_id']) ? $row['order_id'] : (isset($row['pid']) ? $row['pid'] : 'N/A');
                    $status = strtolower($row['status'] ?? '');
                    // Status badge styling
                    $badge_color = 'secondary';
                    if($status == 'pending' || $status == 'order placed') {
                        $badge_color = 'warning';
                    } elseif($status == 'processing' || $status == 'confirmed') {
                        $badge_color = 'info';
                    } elseif($status == 'shipped') {
                        $badge_color = 'primary';
                    } elseif($status == 'delivered' || $status == 'completed') {
                        $badge_color = 'success';
                    } elseif($status == 'cancelled' || $status == 'rejected') {
                        $badge_color = 'danger';
                    }
                    $display_qty = intval($row['pqty'] ?? 0);
                    if ($display_qty < 1) $display_qty = 1;
                    $total = $row['pprice'] * $display_qty;
                    echo '<tr>';
                    echo '<td><strong>#'.htmlspecialchars($order_id).'</strong></td>';
                    echo '<td>'.htmlspecialchars($row['pname'] ?? 'N/A').'</td>';
                    echo '<td>'.htmlspecialchars($row['name'] ?? 'N/A').'</td>';
                    echo '<td>'.htmlspecialchars($row['user'] ?? 'N/A').'</td>';
                    echo '<td>'.htmlspecialchars($display_qty).'</td>';
                    echo '<td>₹'.htmlspecialchars(number_format($row['pprice'],2)).'</td>';
                    echo '<td>₹'.htmlspecialchars(number_format($total,2)).'</td>';
                    echo '<td>'.htmlspecialchars($row['payment_method'] ?? 'N/A').'</td>';

                    $order_date = !empty($row['pdate']) ? date('d M Y, H:i', strtotime($row['pdate'])) : (!empty($row['created_at']) ? date('d M Y, H:i', strtotime($row['created_at'])) : '');
                    echo '<td>'.htmlspecialchars($order_date).'</td>';

                    echo '<td>'.htmlspecialchars(!empty($row['delivered_at']) ? date('d M Y, H:i', strtotime($row['delivered_at'])) : '').'</td>';

                    echo '<td>'.htmlspecialchars(!empty($row['canceled_at']) ? date('d M Y, H:i', strtotime($row['canceled_at'])) : '').'</td>';

                    echo '<td><span class="badge bg-'.$badge_color.'">'.htmlspecialchars($row['status']).'</span></td>';

                    echo '<form action="orderstatus.php" method="post" style="display: inline;">';
                    echo '<input type="hidden" name="order_id" value="'.htmlspecialchars($order_id).'">';
                    $prod_id_val = isset($row['prod_id']) ? $row['prod_id'] : (isset($row['pid']) ? $row['pid'] : '');
                    echo '<input type="hidden" name="prod_id" value="'.htmlspecialchars($prod_id_val).'">';
                    echo '</form> ';
                    echo '<form action="myorder_details.php" method="post" style="display: inline;">';
                    echo '<input type="hidden" name="order_id" value="'.htmlspecialchars($order_id).'">';
                    
                    echo '</form>';
                    echo '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="11" class="text-center">No history found</td></tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include('footer.php'); ?>
