<?php
/* 
doc: https: //www.w3schools.com/howto/howto_css_image_text.asp 
doc: https: //www.w3schools.com/icons/tryit.asp?filename=tryicons_google-collections
doc: https: //www.w3docs.com/snippets/css/how-to-make-a-div-a-clickable-link.html
doc: https://stackoverflow.com/questions/796087/make-a-div-into-a-link
doc: https://developers.google.com/fonts/docs/material_symbols#getting_material_symbols

problem not fixed: https://github.com/google/material-design-icons/issues/1834

ATTENTION: Google's Material Symbols are fiddly to deal with, so watch out !
*/

/** 
 * @var Member $user
 * @var Bid $bid
 * @var Item $item
 * @var ItemStatus $item_status
 * @var array $my_bids
 * @var array $available_items
 */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Browse</title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=add,celebration,filter,gavel,home,schedule,settings,shopping_bag,travel_explore,trophy" />
    <link rel="stylesheet" href="css/browse.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="margin">
        <!-- MY BIDS -->
        <?php if ($user->get_id() !== null && !empty($my_bids)): ?>
            <h1>My Bids</h1>
            <div class="browse">
                <?php foreach ($my_bids as $bid) {
                    $item_status = $bid->get_item_status();
                    $item = $item_status->get_item();
                    require 'item_card.php';
                } ?>
            </div>
        <?php endif; ?>
        
        <!-- AVAILABLE ITEMS -->
        <?php if (!empty($available_items)): ?>
            <h1>Available Items</h1>
            <div class="browse">
                <?php unset($bid); //necessary otherwise the last bid will show in available items (weird !) 
                ?>
                <?php foreach ($available_items as $item_status) {
                    $item = $item_status->get_item();
                    require 'item_card.php';
                } ?>
            </div>

        <!-- NO AVAILABLE ITEMS -->
        <?php else: ?>
            <p>Nothing here but chickens !</p>
        <?php endif; ?>

        <p><a href='placeholder/logout_user'>Logout</a>
    </div>

    <!-- NAVBAR MENU -->
    <?php require 'menu.php' ?>

</body>

</html>