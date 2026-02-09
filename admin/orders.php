<?php
include('header.php');
include('conn.php');

// Handle cancel action (admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $order_id = intval($_POST['cancel_order_id']);

    // Start a transaction
    mysqli_begin_transaction($con);
    $error = '';

    // Determine PK style and load purchase
    $purchase = null;
    $pk_check = mysqli_query($con, "SHOW COLUMNS FROM purchase LIKE 'order_id'");
    $has_order_id = ($pk_check && mysqli_num_rows($pk_check) > 0);
    if ($has_order_id) {
        $pres = mysqli_query($con, "SELECT * FROM purchase WHERE order_id = $order_id LIMIT 1");
    } else {
        $pres = mysqli_query($con, "SELECT * FROM purchase WHERE pid = $order_id LIMIT 1");
    }
    if ($pres && mysqli_num_rows($pres) > 0) {
        $purchase = mysqli_fetch_assoc($pres);
    } else {
        $error = 'Purchase not found';
    }

    // Disallow cancelling customization orders
    if (empty($error) && !empty($purchase['customization_id'])) {
        $error = 'Cannot cancel customization orders';
    }

    // Ensure canceled_at exists
    if (empty($error)) {
        $col_check_purchase = mysqli_query($con, "SHOW COLUMNS FROM purchase LIKE 'canceled_at'");
        if (!$col_check_purchase || mysqli_num_rows($col_check_purchase) === 0) {
            mysqli_query($con, "ALTER TABLE purchase ADD COLUMN canceled_at DATETIME NULL");
        }
        $col_check_myorder = mysqli_query($con, "SHOW COLUMNS FROM myorder LIKE 'canceled_at'");
        if (!$col_check_myorder || mysqli_num_rows($col_check_myorder) === 0) {
            mysqli_query($con, "ALTER TABLE myorder ADD COLUMN canceled_at DATETIME NULL");
        }
    }

    // Update purchase
    if (empty($error)) {
        if ($has_order_id) {
            $pstmt = mysqli_prepare($con, "UPDATE purchase SET status='cancelled', canceled_at=NOW() WHERE order_id = ?");
        } else {
            $pstmt = mysqli_prepare($con, "UPDATE purchase SET status='cancelled', canceled_at=NOW() WHERE pid = ?");
        }
        if ($pstmt) {
            mysqli_stmt_bind_param($pstmt, 'i', $order_id);
            if (!mysqli_stmt_execute($pstmt)) {
                $error = 'Could not update purchase: ' . mysqli_stmt_error($pstmt);
            }
            mysqli_stmt_close($pstmt);
        } else {
            $error = 'Could not prepare purchase update: ' . mysqli_error($con);
        }
    }

    // Update myorder
    if (empty($error)) {
        $mstmt = mysqli_prepare($con, "UPDATE myorder SET status='cancelled', canceled_at=NOW() WHERE order_id = ?");
        if ($mstmt) {
            mysqli_stmt_bind_param($mstmt, 'i', $order_id);
            if (!mysqli_stmt_execute($mstmt)) {
                $error = 'Could not update myorder: ' . mysqli_stmt_error($mstmt);
                mysqli_stmt_close($mstmt);
            } else {
                $affected = mysqli_stmt_affected_rows($mstmt);
                mysqli_stmt_close($mstmt);
                if ($affected === 0) {
                    // Try prod_id + user fallback if not customization
                    $prod_id = intval($purchase['prod_id'] ?? 0);
                    $puser = mysqli_real_escape_string($con, $purchase['user'] ?? '');
                    if ($prod_id > 0) {
                        $alt_update_sql = "UPDATE myorder SET status='cancelled', canceled_at=NOW() WHERE prod_id = $prod_id AND user = '$puser' AND status <> 'cancelled'";
                        if (mysqli_query($con, $alt_update_sql) === false) {
                            $error = 'Could not update myorder by prod_id/user: ' . mysqli_error($con);
                        }
                    }
                    // If still no match, insert cancelled myorder using purchase
                    if (empty($error)) {
                        $check_sql = "SELECT order_id FROM myorder WHERE order_id = '$order_id' LIMIT 1";
                        $check_res = mysqli_query($con, $check_sql);
                        if (!$check_res || mysqli_num_rows($check_res) === 0) {
                            if ($purchase) {
                                $pname = mysqli_real_escape_string($con, $purchase['pname'] ?? '');
                                $puser = mysqli_real_escape_string($con, $purchase['user'] ?? '');
                                $pname_field = mysqli_real_escape_string($con, $purchase['name'] ?? '');
                                $pprice = mysqli_real_escape_string($con, $purchase['pprice'] ?? '0');
                                $pqty = intval($purchase['pqty'] ?? 1);
                                $prod_id = intval($purchase['prod_id'] ?? 0);
                                $pdate = mysqli_real_escape_string($con, $purchase['pdate'] ?? date('Y-m-d H:i:s'));
                                $ins_sql = "INSERT INTO myorder (order_id, pname, user, name, pprice, pqty, prod_id, customization_id, status, pdate, canceled_at, created_at) VALUES ('$order_id', '$pname', '$puser', '$pname_field', '$pprice', '$pqty', '$prod_id', '0', 'cancelled', '$pdate', NOW(), NOW())";
                                if (!mysqli_query($con, $ins_sql)) {
                                    $error = 'Could not insert myorder: ' . mysqli_error($con);
                                }
                            } else {
                                $error = 'Purchase record missing; cannot create myorder';
                            }
                        }
                    }
                }
            }
        } else {
            $error = 'Could not prepare myorder update: ' . mysqli_error($con);
        }
    }

    if (empty($error)) {
        mysqli_commit($con);
        echo "<script>window.location.href='orders.php?msg=".urlencode('Order cancelled successfully')."';</script>";
        exit;
    } else {
        mysqli_rollback($con);
        $error = 'Could not cancel order: ' . $error;
    }
}

// Handle mark delivered action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deliver_order_id'])) {
    $order_id = intval($_POST['deliver_order_id']);

    // Start a transaction so both tables are updated together
    mysqli_begin_transaction($con);
    $error = '';

    // Determine whether `purchase` uses `order_id` or `pid` as PK and fetch the existing row (used to insert into myorder if needed)
    $purchase = null;
    $pk_check = mysqli_query($con, "SHOW COLUMNS FROM purchase LIKE 'order_id'");
    $has_order_id = ($pk_check && mysqli_num_rows($pk_check) > 0);
    if ($has_order_id) {
        $pres = mysqli_query($con, "SELECT * FROM purchase WHERE order_id = $order_id LIMIT 1");
    } else {
        $pres = mysqli_query($con, "SELECT * FROM purchase WHERE pid = $order_id LIMIT 1");
    }
    if ($pres && mysqli_num_rows($pres) > 0) {
        $purchase = mysqli_fetch_assoc($pres);
    }

    // Ensure delivered_at column exists on purchase and myorder tables
    $col_check_purchase = mysqli_query($con, "SHOW COLUMNS FROM purchase LIKE 'delivered_at'");
    if (!$col_check_purchase || mysqli_num_rows($col_check_purchase) === 0) {
        mysqli_query($con, "ALTER TABLE purchase ADD COLUMN delivered_at DATETIME NULL");
    }
    $col_check_myorder = mysqli_query($con, "SHOW COLUMNS FROM myorder LIKE 'delivered_at'");
    if (!$col_check_myorder || mysqli_num_rows($col_check_myorder) === 0) {
        mysqli_query($con, "ALTER TABLE myorder ADD COLUMN delivered_at DATETIME NULL");
    }

    // Update purchase table using the correct PK and set delivered_at
    if ($has_order_id) {
        $pstmt = mysqli_prepare($con, "UPDATE purchase SET status='delivered', delivered_at=NOW() WHERE order_id = ?");
    } else {
        $pstmt = mysqli_prepare($con, "UPDATE purchase SET status='delivered', delivered_at=NOW() WHERE pid = ?");
    }
    if ($pstmt) {
        mysqli_stmt_bind_param($pstmt, 'i', $order_id);
        if (!mysqli_stmt_execute($pstmt)) {
            $error = 'Could not update purchase: ' . mysqli_stmt_error($pstmt);
        }
        mysqli_stmt_close($pstmt);
    } else {
        $error = 'Could not prepare purchase update: ' . mysqli_error($con);
    }

    // Update myorder table (user-visible copy). If no row affected, try alternate matches (customization_id or prod_id+user), otherwise insert
    if (empty($error)) {
        $mstmt = mysqli_prepare($con, "UPDATE myorder SET status='delivered', delivered_at=NOW() WHERE order_id = ?");
        if ($mstmt) {
            mysqli_stmt_bind_param($mstmt, 'i', $order_id);
            if (!mysqli_stmt_execute($mstmt)) {
                $error = 'Could not update myorder: ' . mysqli_stmt_error($mstmt);
                mysqli_stmt_close($mstmt);
            } else {
                $affected = mysqli_stmt_affected_rows($mstmt);
                mysqli_stmt_close($mstmt);
                if ($affected === 0) {
                    // Try update by customization_id if present (set delivered_at)
                    if ($purchase && !empty($purchase['customization_id'])) {
                        $cid = intval($purchase['customization_id']);
                        $puser = mysqli_real_escape_string($con, $purchase['user'] ?? '');
                        $alt_sql = "UPDATE myorder SET status='delivered', delivered_at=NOW() WHERE customization_id = $cid AND user = '$puser' AND status <> 'delivered'";
                        if (mysqli_query($con, $alt_sql) === false) {
                            $error = 'Could not update myorder by customization_id: ' . mysqli_error($con);
                        } elseif (mysqli_affected_rows($con) === 0) {
                            // No match - fall through to insert below
                        }
                    }
                    // If still no rows affected, try prod_id + user
                    if (empty($error) && $purchase && empty($purchase['customization_id'])) {
                        $prod_id = intval($purchase['prod_id'] ?? 0);
                        $puser = mysqli_real_escape_string($con, $purchase['user'] ?? '');
                        $alt_update_sql = "UPDATE myorder SET status='delivered', delivered_at=NOW() WHERE prod_id = $prod_id AND user = '$puser' AND status <> 'delivered'";
                        if (mysqli_query($con, $alt_update_sql) === false) {
                            $error = 'Could not update myorder by prod_id/user: ' . mysqli_error($con);
                        } elseif (mysqli_affected_rows($con) === 0) {
                            // still no match; insert below
                        }
                    }

                    // If still nothing updated, insert a delivered myorder row using purchase data (with delivered_at)
                    if (empty($error)) {
                        // check again whether any myorder row now exists
                        $check_sql = "SELECT order_id FROM myorder WHERE order_id = '$order_id' LIMIT 1";
                        $check_res = mysqli_query($con, $check_sql);
                        if (!$check_res || mysqli_num_rows($check_res) === 0) {
                            if ($purchase) {
                                $pname = mysqli_real_escape_string($con, $purchase['pname'] ?? '');
                                $puser = mysqli_real_escape_string($con, $purchase['user'] ?? '');
                                $pname_field = mysqli_real_escape_string($con, $purchase['name'] ?? '');
                                $pprice = mysqli_real_escape_string($con, $purchase['pprice'] ?? '0');
                                $pqty = intval($purchase['pqty'] ?? 1);
                                $prod_id = intval($purchase['prod_id'] ?? 0);
                                $pdate = mysqli_real_escape_string($con, $purchase['pdate'] ?? date('Y-m-d H:i:s'));
                                $ins_sql = "INSERT INTO myorder (order_id, pname, user, name, pprice, pqty, prod_id, customization_id, status, pdate, delivered_at, created_at) VALUES ('$order_id', '$pname', '$puser', '$pname_field', '$pprice', '$pqty', '$prod_id', '" . (intval($purchase['customization_id'] ?? 0)) . "', 'delivered', '$pdate', NOW(), NOW())";
                                if (!mysqli_query($con, $ins_sql)) {
                                    $error = 'Could not insert myorder: ' . mysqli_error($con);
                                }
                            } else {
                                $error = 'Purchase record missing; cannot create myorder';
                            }
                        }
                    }
                }
            }
        } else {
            $error = 'Could not prepare myorder update: ' . mysqli_error($con);
        }
    }

    if (empty($error)) {
        mysqli_commit($con);
        echo "<script>window.location.href='orders.php?msg=".urlencode('Order marked as delivered')."';</script>";
        exit;
    } else {
        mysqli_rollback($con);
        $error = 'Could not update order status: ' . $error;
    }
}

?>
<div class="container" style="margin-top:100px;">
  <h2 class="mb-3">Orders</h2>
  <!-- Toast container for cancellation notifications -->
  <div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index: 1200;">
    <div id="cancelToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header">
        <strong class="me-auto">New Cancellation</strong>
        <small class="text-muted">just now</small>
        <button type="button" class="btn-close ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">
        <div id="cancelToastBody">No details</div>
        <div class="mt-2 pt-2 border-top">
          <button id="ackCancelBtn" class="btn btn-sm btn-primary">Mark reviewed</button>
        </div>
      </div>
    </div>
  </div>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
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
              <th>User Name</th>
              <th>Email</th>
              <th>Qty</th>
              
              <th>Order Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sql = "SELECT purchase.*, COALESCE(product.pname, purchase.pname) as pname FROM purchase LEFT JOIN product ON purchase.prod_id = product.pid WHERE (purchase.status NOT IN ('delivered','cancelled') OR purchase.status IS NULL) ORDER BY purchase.order_id DESC";
            $res = mysqli_query($con, $sql);
            if ($res && mysqli_num_rows($res) > 0) {
                while ($r = mysqli_fetch_assoc($res)) {
                    $oid = $r['order_id'] ?? $r['pid'] ?? '';
                    echo '<tr>';
                    echo '<td>#'.htmlspecialchars($oid).'</td>';
                    echo '<td>'.htmlspecialchars($r['pname'] ?? 'N/A').'</td>';
                    echo '<td>'.htmlspecialchars($r['name'] ?? 'N/A').'</td>';
                    echo '<td>'.htmlspecialchars($r['user'] ?? 'N/A').'</td>';
                  // Determine quantity: prefer purchase.pqty, otherwise fall back to matching customization.quantity
                  $display_qty = intval($r['pqty'] ?? 0);
                  if ($display_qty < 1) {
                    $safe_user = mysqli_real_escape_string($con, $r['user'] ?? '');
                    $safe_pname = mysqli_real_escape_string($con, $r['pname'] ?? '');
                    $qc = mysqli_query($con, "SELECT pqty FROM customization WHERE status='accepted' AND (email='$safe_user' OR name='$safe_user') AND product_type LIKE '%$safe_pname%' ORDER BY created_at DESC LIMIT 1");
                    if ($qc && mysqli_num_rows($qc) > 0) {
                      $qrow = mysqli_fetch_assoc($qc);
                      $display_qty = intval($qrow['pqty'] ?? 0);
                    }
                    if ($display_qty < 1) $display_qty = 1;
                  }
                  echo '<td>'.htmlspecialchars($display_qty).'</td>';
                    
                    echo '<td>'.htmlspecialchars($r['pdate'] ?? $r['created_at'] ?? '').'</td>';
                    echo '<td>';
                    // Mark Delivered
                    echo '<form method="post" style="display:inline-block">';
                    echo '<input type="hidden" name="deliver_order_id" value="'.htmlspecialchars($oid).'">';
                    echo '<button class="btn btn-sm btn-success"><i class="bi bi-truck me-1"></i>Mark Delivered</button>';
                    echo '</form> ';
                    // Cancel (only for normal orders without customization_id)
                    $can_cancel_admin = empty($r['customization_id']);
                    if ($can_cancel_admin) {
                        echo '<form method="post" style="display:inline-block" onsubmit="return confirm(\'Are you sure you want to cancel this order?\')">';
                        echo '<input type="hidden" name="cancel_order_id" value="'.htmlspecialchars($oid).'">';
                        echo '<button class="btn btn-sm btn-outline-danger ms-1"><i class="bi bi-x-circle me-1"></i>Cancel</button>';
                        echo '</form> ';
                    }
                    // Details (open admin details page)
                    echo '<form method="get" action="order_details.php" style="display:inline-block">';
                    echo '<input type="hidden" name="order_id" value="'.htmlspecialchars($oid).'">';
                    echo '<button type="submit" class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-eye me-1"></i>Details</button>';
                    echo '</form>';
                    echo '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="7" class="text-center">No pending orders</td></tr>';
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
              <th> User Name</th>
              <th>Email</th>
              <th>Qty</th>
              <th>Order Date</th>
              <th>Delivered Date</th>
              <th>Cancelled Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sql2 = "SELECT purchase.*, COALESCE(product.pname, purchase.pname) as pname FROM purchase LEFT JOIN product ON purchase.prod_id = product.pid WHERE purchase.status IN ('delivered','cancelled') ORDER BY purchase.order_id DESC";
            $res2 = mysqli_query($con, $sql2);
            if ($res2 && mysqli_num_rows($res2) > 0) {
                while ($r = mysqli_fetch_assoc($res2)) {
                    $oid = $r['order_id'] ?? $r['pid'] ?? '';
                    echo '<tr>';
                    echo '<td>#'.htmlspecialchars($oid).'</td>';
                    echo '<td>'.htmlspecialchars($r['pname'] ?? 'N/A').'</td>';
                    echo '<td>'.htmlspecialchars($r['name'] ?? 'N/A').'</td>';
                    echo '<td>'.htmlspecialchars($r['user'] ?? 'N/A').'</td>';
                  // Determine quantity for history rows as well
                  $display_qty = intval($r['pqty'] ?? 0);
                  if ($display_qty < 1) {
                    $safe_user = mysqli_real_escape_string($con, $r['user'] ?? '');
                    $safe_pname = mysqli_real_escape_string($con, $r['pname'] ?? '');
                    $qc = mysqli_query($con, "SELECT pqty FROM customization WHERE status='accepted' AND (email='$safe_user' OR name='$safe_user') AND product_type LIKE '%$safe_pname%' ORDER BY created_at DESC LIMIT 1");
                    if ($qc && mysqli_num_rows($qc) > 0) {
                      $qrow = mysqli_fetch_assoc($qc);
                      $display_qty = intval($qrow['pqty'] ?? 0);
                    }
                    if ($display_qty < 1) $display_qty = 1;
                  }
                  echo '<td>'.htmlspecialchars($display_qty).'</td>';
                    $order_date_admin = !empty($r['pdate']) ? date('d M Y, H:i', strtotime($r['pdate'])) : (!empty($r['created_at']) ? date('d M Y, H:i', strtotime($r['created_at'])) : '');
                    echo '<td>'.htmlspecialchars($order_date_admin).'</td>';
                    echo '<td>'.htmlspecialchars(!empty($r['delivered_at']) ? date('d M Y, H:i', strtotime($r['delivered_at'])) : '').'</td>';
                    echo '<td>'.htmlspecialchars(!empty($r['canceled_at']) ? date('d M Y, H:i', strtotime($r['canceled_at'])) : '').'</td>';
                    $status_label = htmlspecialchars($r['status'] ?? '');
                    $badge = 'secondary';
                    if (strtolower($status_label) == 'delivered') $badge = 'success';
                    if (strtolower($status_label) == 'cancelled') $badge = 'danger';
                    echo '<td><span class="badge bg-'.$badge.'">'.htmlspecialchars(ucfirst($status_label)).'</span></td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="9" class="text-center">No history found</td></tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  
</div>

<script>
(function(){
  const toastEl = document.getElementById('cancelToast');
  const toastBody = document.getElementById('cancelToastBody');
  const ackBtn = document.getElementById('ackCancelBtn');
  const bsToast = new bootstrap.Toast(toastEl, {delay: 10000});
  let currentIds = [];

  async function pollCancellations(){
    try{
      const res = await fetch('check_cancellations.php');
      if(!res.ok) return;
      const data = await res.json();
      if(data.count && data.count > 0){
        // Build message
        const list = data.orders.map(o => '<div><strong>#' + (o.order_id || '') + '</strong> — ' + (o.pname || 'N/A') + ' by ' + (o.name || o.user || '') + ' <small class="text-muted">(' + (new Date(o.canceled_at)).toLocaleString() + ')</small></div>').join('');
        toastBody.innerHTML = '<div>' + data.count + ' new cancellation(s)</div>' + list;
        currentIds = data.orders.map(o=>o.order_id).filter(Boolean);
        bsToast.show();
      }
    } catch(e) {
      console.error('Polling error', e);
    }
  }

  ackBtn.addEventListener('click', async function(){
    if (!currentIds.length) return;
    try{
      const form = new FormData();
      currentIds.forEach(id => form.append('order_ids[]', id));
      const res = await fetch('ack_cancellations.php', {method:'POST', body:form});
      const data = await res.json();
      if (data.success){
        bsToast.hide();
        // clear currentIds
        currentIds = [];
      } else {
        alert('Could not acknowledge: ' + (data.message || 'Unknown'));
      }
    } catch(e){
      console.error('Ack error', e);
    }
  });

  // Initial poll + interval
  pollCancellations();
  setInterval(pollCancellations, 15000);
})();
</script>
<?php include('footer.php'); ?>
