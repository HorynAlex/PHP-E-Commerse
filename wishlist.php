<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
    header('location:login.php');
}

if(isset($_POST['add_to_cart'])){
    $pid = $_POST['pid'];
    $pid = filter_var($pid, FILTER_SANITIZE_STRING);
    $p_name = $_POST['p_name'];
    $p_name = filter_var($p_name, FILTER_SANITIZE_STRING);
    $p_price = $_POST['p_price'];
    $p_price = filter_var($p_price, FILTER_SANITIZE_STRING);
    $p_image = $_POST['p_image'];
    $p_image = filter_var($p_image, FILTER_SANITIZE_STRING);
    $p_qty = $_POST['p_qty'];
    $p_qty = filter_var($p_qty, FILTER_SANITIZE_STRING);

    $check_cart_number = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
    $check_cart_number->execute([$p_name, $user_id]);

    if($check_cart_number->rowCount() > 0){
        $message[] = 'already added to cart';
    } else{

        $check_wishlist_number = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
        $check_wishlist_number->execute([$p_name, $user_id]);

        if($check_wishlist_number->rowCount() > 0){
            $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
            $delete_wishlist->execute([$p_name, $user_id]);
        }

        $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
        $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
        $message[] = 'added to cart';
    }

}

if(isset($_GET['delete'])){

    $delete_id = $_GET['delete'];
    $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE id = ?");
    $delete_wishlist_item->execute([$delete_id]);
    header('location:wishlist.php');

}

if(isset($_GET['delete_all'])){

    $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
    $delete_wishlist_item->execute([$user_id]);
    header('location:wishlist.php');

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>wishlist</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="wishlist">

    <h1 class="title">products added</h1>
    
    <div class="box-container">

        <?php
            $grand_total = 0;
            $select_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
            $select_wishlist->execute([$user_id]);

            if($select_wishlist->rowCount() > 0){
                while($fetch_wishist = $select_wishlist->fetch(PDO::FETCH_ASSOC)){

        ?>
        <form action="" method="POST" class="box">
            <a href="wishlist.php?delete=<?= $fetch_wishist['id']; ?>" class="fas fa-times" onclick="return confirm('delete this from wishlist!')"></a>
            <a href="view_page.php?pid=<?= $fetch_wishist['pid']; ?>" class="fas fa-eye"></a>
            <img src="uploaded_img/<?= $fetch_wishist['image']; ?>" alt="">
            <div class="name"><?= $fetch_wishist['name']; ?></div>
            <div class="price">$ <span><?=$fetch_wishist['price']; ?></span> /-</div>
            <input type="number" min="1" value="1" class="qty" name="p_qty">
            <input type="hidden" name="pid" value="<?= $fetch_wishist['pid']; ?>">
            <input type="hidden" name="p_name" value="<?= $fetch_wishist['name']; ?>">
            <input type="hidden" name="p_price" value="<?= $fetch_wishist['price']; ?>">
            <input type="hidden" name="p_image" value="<?= $fetch_wishist['image']; ?>">
            <input type="submit" value="add to cart" name="add_to_cart" class="btn">
        </form>
        <?php
            $grand_total += $fetch_wishist['price'];
                }
            } else{
            echo '<p class="empty">your wishlist empty!</p>';
            }
        ?>

    </div>

    <div class="wishlist-total">
        <p>grand total: <span>$<?= $grand_total; ?></span></p>
        <a href="shop.php" class="option-btn">continue shopping</a>
        <a href="wishlist.php?delete_all" class="delete-btn <?= ($grand_total > 1)?'': 'disabled'; ?>">delete all</a>
    </div>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
    
</body>
</html>