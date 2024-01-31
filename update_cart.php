<?php
// Include necessary files and connect to the database
require('config.php'); // Include your database connection configuration file
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize the posted data
    $productId = $_POST['productId'];
    $newSizeId = $_POST['newSizeId'];
    $newColorId = $_POST['newColorId'];
    $newQuantity = $_POST['newQuantity'];

    // Assuming you have a function to update the cart in your application
    updateCart($productId, $newSizeId, $newColorId, $newQuantity);

    // Send a response back to the AJAX request
    $response = array('status' => 'success', 'message' => 'Cart updated successfully');
    echo json_encode($response);
} else {
    // Handle invalid requests
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'Invalid request'));
}

// Function to update the cart in the database
function updateCart($productId, $newSizeId, $newColorId, $newQuantity) {
    // Include your database connection logic
    require('db_connection.php');

    // Assuming you have a table named 'cart' with columns: id, user_id, product_id, size_id, color_id, quantity
    $userId = $_SESSION['user_id']; // Assuming you store user_id in the session

    // Check if the product is already in the cart
    $query = "SELECT * FROM cart WHERE user_id = $userId AND product_id = $productId AND size_id = $newSizeId AND color_id = $newColorId";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // Update the existing entry
        $updateQuery = "UPDATE cart SET quantity = $newQuantity WHERE user_id = $userId AND product_id = $productId AND size_id = $newSizeId AND color_id = $newColorId";
        mysqli_query($conn, $updateQuery);
    } else {
        // Insert a new entry
        $insertQuery = "INSERT INTO cart (user_id, product_id, size_id, color_id, quantity) VALUES ($userId, $productId, $newSizeId, $newColorId, $newQuantity)";
        mysqli_query($conn, $insertQuery);
    }

    // Close the database connection
    mysqli_close($conn);
}
?>
