<?php
require('connection.inc.php');
require('functions.inc.php');
require('add_to_cart.inc.php');

// Check if the user is logged in
if (isset($_SESSION['USER_LOGIN'])) {
    $uid = $_SESSION['USER_ID'];
} else {
    echo json_encode(array('status' => 'error', 'message' => 'User not logged in'));
    exit;
}

$pid = get_safe_value($con, $_POST['pid']);
$qty = get_safe_value($con, $_POST['qty']);
$type = get_safe_value($con, $_POST['type']);

$attr_id = 0;
if (isset($_POST['sid']) && isset($_POST['cid'])) {
    $sub_sql = '';
    $sid = get_safe_value($con, $_POST['sid']);
    $cid = get_safe_value($con, $_POST['cid']);
    if ($sid > 0) {
        $sub_sql .= " and size_id = $sid ";
    }
    if ($cid > 0) {
        $sub_sql .= " and color_id = $cid ";
    }
    $row = mysqli_fetch_assoc(mysqli_query($con, "select id from product_attributes where product_id = '$pid' $sub_sql"));
    $attr_id = $row['id'];
}

$productSoldQtyByProductId = productSoldQtyByProductId($con, $pid, $attr_id);
$productQty = productQty($con, $pid, $attr_id);

$pending_qty = $productQty - $productSoldQtyByProductId;

if ($qty > $pending_qty && $type != 'remove') {
    echo json_encode(array('status' => 'error', 'message' => 'Quantity not available'));
    die();
}

$obj = new add_to_cart();
if (isset($_SESSION['USER_LOGIN'])) {
    $uid = $_SESSION['USER_ID'];

    if ($type == 'add') {
        $obj->addProduct($pid, $qty, $attr_id);
        $sql = "INSERT INTO cart (user_id, product_id, quantity, attr_id) VALUES ('$uid', '$pid', '$qty', '$attr_id')";
        if ($con->query($sql) === TRUE) {
            echo json_encode(array('status' => 'success', 'message' => 'Record inserted successfully into cart table'));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Error inserting record: ' . $con->error));
        }
    }

    if ($type == 'remove') {
        $obj->removeProduct($pid, $attr_id);
        $sql = "DELETE FROM cart WHERE user_id = '$uid' AND product_id = '$pid' AND attr_id = '$attr_id'";
        if ($con->query($sql) === TRUE) {
            echo json_encode(array('status' => 'success', 'message' => 'Record removed successfully from cart table'));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Error removing record: ' . $con->error));
        }
    }

    if ($type == 'update') {
        $obj->updateProduct($pid, $qty, $attr_id);
        $sql = "UPDATE cart SET quantity = '$qty' WHERE user_id = '$uid' AND product_id = '$pid' AND attr_id = '$attr_id'";
        if ($con->query($sql) === TRUE) {
            echo json_encode(array('status' => 'success', 'message' => 'Record updated successfully in cart table'));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Error updating record: ' . $con->error));
        }
    }
}

$user_id = $_SESSION['USER_ID'];

$totalProducts = $obj->totalProduct($user_id);
echo json_encode(array('totalProduct' => $totalProducts));
?>
