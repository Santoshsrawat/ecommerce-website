<?php
require('top.php');
?>

<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- Your HTML code -->
<div class="ht_bradcaump_area" style="background: rgba(0, 0, 0, 0) url(images/bg/4.jpg) no-repeat scroll center center / cover;">
   <div class="ht_bradcaump_wrap">
      <div class="container">
         <div class="row">
            <div class="col-xs-12">
               <div class="bradcaump__inner">
                  <nav class="bradcaump-inner">
                     <a class="breadcrumb-item" href="index.php">Home</a>
                     <span class="brd-separetor"><i class="zmdi zmdi-chevron-right"></i></span>
                     <span class="breadcrumb-item active">Shopping Cart</span>
                  </nav>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<!-- End Bradcaump area -->

<!-- Cart Main Area -->
<div class="cart-main-area ptb--100 bg__white">
   <div class="container">
      <div class="row">
         <div class="col-md-12 col-sm-12 col-xs-12">
            <form action="#">
               <div class="table-content table-responsive">
                  <table>
                     <thead>
                        <tr>
                           <th class="product-thumbnail">Products</th>
                           <th class="product-name">Name of Products</th>
                           <th class="product-price">Price</th>
                           <th class="product-quantity">Quantity</th>
                           <th class="product-subtotal">Total</th>
                           <th class="product-remove">Remove</th>
                        </tr>
                     </thead>
                    <tbody>
    <?php
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $val) {
            foreach ($val as $key1 => $val1) {
                // Fetch product attributes from the database
                $resAttr = mysqli_fetch_assoc(mysqli_query($con, "SELECT product_attributes.*, color_master.color, size_master.size FROM product_attributes 
                                LEFT JOIN color_master ON product_attributes.color_id = color_master.id AND color_master.status = 1 
                                LEFT JOIN size_master ON product_attributes.size_id = size_master.id AND size_master.status = 1
                                WHERE product_attributes.id = '$key1'"));

                // Fetch product details using a function
                $productArr = get_product($con, '', '', $key, '', '', '', '', $key1);

                // Check if the product details are available
                if ($product = $productArr[0] ?? null) {
                    $pname = $product['name'];
                    $mrp = $product['mrp'];
                    $price = $product['price'];
                    $image = $product['image'];
                    $qty = $val1['qty'] ?? 0;

                    // Output the table row
                    ?>
                    <tr>
                        <td class="product-thumbnail"><a href="#"><img src="<?php echo PRODUCT_IMAGE_SITE_PATH . $image ?>" /></a></td>
                        <td class="product-name">
                            <a href="#"><?php echo $pname ?></a>
                            <?php
                            if (isset($resAttr['color']) && $resAttr['color'] != '') {
                                echo "<br/>" . $resAttr['color'];
                            }
                            if (isset($resAttr['size']) && $resAttr['size'] != '') {
                                echo "<br/>" . $resAttr['size'];
                            }
                            ?>
                            <ul class="pro__prize">
                                <li class="old__prize"><?php echo $mrp ?></li>
                                <li><?php echo $price ?></li>
                            </ul>
                        </td>
                        <td class="product-price" id="<?php echo $key ?>price"><span class="amount"><?php echo $price ?></span></td>
                        <td class="product-quantity">
                            <input type="number" id="<?php echo $key ?>qty" value="<?php echo $qty ?>" onchange="updateCart('<?php echo $key ?>', '<?php echo $resAttr['size_id'] ?>', '<?php echo $resAttr['color_id'] ?>')" />
                        </td>
                        <td class="product-subtotal" id="<?php echo $key ?>subtotal"><?php echo $qty * $price ?></td>
                        <td class="product-remove">
                            <a href="javascript:void(0)" onclick="manage_cart_update('<?php echo $key ?>','remove','<?php echo $resAttr['size_id'] ?>','<?php echo $resAttr['color_id'] ?>')">
                                <i class="icon-trash icons"></i>
                            </a>
                        </td>
                    </tr>
                <?php
                }
            }
        }
    }
    ?>
</tbody>

                  </table>
               </div>
               <div class="row">
                  <div class="col-md-12 col-sm-12 col-xs-12">
                     <div class="buttons-cart--inner">
                        <div class="buttons-cart">
                           <a href="<?php echo SITE_PATH ?>">Continue Shopping</a>
                        </div>
                        <div class="buttons-cart checkout--btn">
                           <a href="<?php echo SITE_PATH ?>checkout.php">Checkout</a>
                        </div>
                     </div>
                  </div>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>
<!-- End Cart Main Area -->

<!-- Additional jQuery inclusion and hidden input fields -->
<input type="hidden" id="sid">
<input type="hidden" id="cid">
<?php require('footer.php') ?>

<script>
    $(document).ready(function () {
        // Change event for quantity input
        $('input[type="number"]').on('change', function () {
            var productId = $(this).attr('id').replace(/qty$/, ''); // Corrected the extraction of product ID
            var newSizeId = '<?php echo $resAttr["size_id"] ?>';
            var newColorId = '<?php echo $resAttr["color_id"] ?>';
            var newQuantity = $(this).val();

            // Send AJAX request
            $.ajax({
                type: 'POST',
                url: 'manage_cart.php',
                data: {
                    pid: productId,
                    type: 'update',
                    qty: newQuantity,
                    cid: newColorId,
                    sid: newSizeId
                },
                success: function (response) {
                    // Parse the JSON response
                    var responseData = JSON.parse(response);

                    // Check if the status is 'success'
                    if (responseData.status === 'success') {
                        // Update the subtotal in the table
                        var newSubtotal = newQuantity * responseData.price;
                        $('#' + productId + 'subtotal').text(newSubtotal);

                        // Optionally, update the displayed price (if needed)
                        $('#' + productId + 'price').text(responseData.price);
                    } else {
                        // Handle error case
                        console.log('Error:', responseData.message);
                    }
                },
                error: function (error) {
                    console.log('Error:', error);
                }
            });
        });
    });
</script>
