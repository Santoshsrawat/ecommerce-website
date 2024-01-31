<?php
require('top.php');

function getProductDetails($con, $key1) {
    $query = "SELECT product_attributes.*, color_master.color, size_master.size FROM product_attributes 
              LEFT JOIN color_master ON product_attributes.color_id = color_master.id AND color_master.status = 1 
              LEFT JOIN size_master ON product_attributes.size_id = size_master.id AND size_master.status = 1
              WHERE product_attributes.id = '$key1'";

    $resAttr = mysqli_fetch_assoc(mysqli_query($con, $query));

    return get_product($con, '', '', $key, '', '', '', '', $key1);
}

?>

<div class="ht__bradcaump__area" style="background: rgba(0, 0, 0, 0) url(images/bg/4.jpg) no-repeat scroll center center / cover ;">

      <div class="container">
         <div class="row">
            <div class="col-xs-12">
               <div class="bradcaump__inner">
                  <nav class="bradcaump-inner">
                     <a class="breadcrumb-item" href="index.php">Home</a>
                     <span class="brd-separetor"><i class="zmdi zmdi-chevron-right"></i></span>
                     <span class="breadcrumb-item active">shopping cart</span>
                  </nav>
               </div>
            </div>
         </div>
      </div>
  
</div>

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
                                    $resAttr = mysqli_fetch_assoc(mysqli_query($con, "SELECT product_attributes.*, color_master.color, size_master.size FROM product_attributes 
                                                                     LEFT JOIN color_master ON product_attributes.color_id = color_master.id AND color_master.status = 1 
                                                                     LEFT JOIN size_master ON product_attributes.size_id = size_master.id AND size_master.status = 1
                                                                     WHERE product_attributes.id = '$key1'"));

                                    $productArr = get_product($con, '', '', $key, '', '', '', '', $key1);
                                    $pname = $productArr[0]['name'];
                                    $mrp = $productArr[0]['mrp'];
                                    $price = $productArr[0]['price'];
                                    $image = $productArr[0]['image'];
                                    $qty = $val1['qty'];
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
                                       <td class="product-price"><span class="amount"><?php echo $price ?></span></td>
                                       <td class="product-quantity">
                                          <input type="number" id="<?php echo $key ?>qty" value="<?php echo $qty ?>" />
                                          <br />
                                          <a href="javascript:void(0)" onclick="updateCart('<?php echo $key ?>', '<?php echo $resAttr['size_id'] ?>', '<?php echo $resAttr['color_id'] ?>')">Update</a>
                                       </td>
                                       <td class="product-subtotal"><?php echo $qty * $price ?></td>
                                       <td class="product-remove"><a href="javascript:void(0)" onclick="manage_cart_update('<?php echo $key ?>','remove','<?php echo $resAttr['size_id'] ?>','<?php echo $resAttr['color_id'] ?>')"><i class="icon-trash icons"></i></a></td>
                                    </tr>
                        <?php
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

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script>
   function manage_cart_update(pid, type, size_id, color_id) {
      $('#cid').val(color_id);
      $('#sid').val(size_id);
      manage_cart(pid, type);
   }

   function manage_cart(pid, type, is_checkout) {
      var is_error = '';
      var qty = (type === 'update') ? $("#" + pid + "qty").val() : $("#qty").val();
      var cid = $('#cid').val();
      var sid = $('#sid').val();

      if (type === 'add') {
         if (is_color !== 0 && cid === '') {
            $('#cart_attr_msg').html('Please select color');
            is_error = 'yes';
         }
         if (is_size !== 0 && sid === '' && is_error === '') {
            $('#cart_attr_msg').html('Please select size');
            is_error = 'yes';
         }
      }

      if (is_error === '') {
         $.ajax({
            url: 'manage_cart.php',
            type: 'post',
            data: 'pid=' + pid + '&qty=' + qty + '&type=' + type + '&cid=' + cid + '&sid=' + sid,
            success: function (result) {
               result = result.trim();
               if (type === 'update' || type === 'remove') {
                  window.location.href = window.location.href;
               }
               if (result === 'not_avaliable') {
                  alert('Qty not available');
               } else {
                  $('.htc__qua').html(result);
                  if (is_checkout === 'yes') {
                     window.location.href = 'checkout.php';
                  }
               }
            },
            error: function (xhr, status, error) {
               console.error(xhr.responseText);
            }
         });
      }
   }
</script>

<input type="hidden" id="sid">
<input type="hidden" id="cid">
<?php require('footer.php') ?>
