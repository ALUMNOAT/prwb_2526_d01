<?php
/* 
doc: https: //www.w3schools.com/howto/howto_css_image_text.asp 
doc: https: //www.w3schools.com/icons/tryit.asp?filename=tryicons_google-collections
doc: https: //www.w3docs.com/snippets/css/how-to-make-a-div-a-clickable-link.html
doc: https://stackoverflow.com/questions/796087/make-a-div-into-a-link
doc: https://developers.google.com/fonts/docs/material_symbols#getting_material_symbols

problem not fixed: https://github.com/google/material-design-icons/issues/1834

ATTENTION: Google's Material Symbols are fiddly to deal with, so watch out !

doc: https://www.w3schools.com/css/css3_pagination.asp
*/

/** 
 * @var Member $user
 * @var Item $item
 * @var ItemStatus $item_status
 * @var array $active_items
 * @var array $closed_unsold_items
 * @var array $closed_sold_items
 */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Browse</title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=add,celebration,filter,gavel,home,schedule,settings,shopping_bag,travel_explore,trophy" />
    <link rel="stylesheet" href="css/my_items.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/time.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class='content'>
        <!-- ACTIVE ITEMS -->
        <?php if (!empty($active_items)): ?>
            <h1>Active Items</h1>
            <div class="browse">
                <?php foreach ($active_items as $item_status) {
                    $item = $item_status->get_item();
                    require 'item_card.php';
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- CLOSED UNSOLD ITEMS -->
        <?php if (!empty($closed_unsold_items)): ?>
            <h1>Unsold Items</h1>
            <div class="browse">
                <?php foreach ($closed_unsold_items as $item_status) {
                    $item = $item_status->get_item();
                    require 'item_card.php';
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- CLOSED SOLD ITEMS -->
        <?php if (!empty($closed_sold_items)): ?>
            <h1>Sold Items</h1>
            <div class="browse">
                <?php foreach ($closed_sold_items as $item_status) {
                    $item = $item_status->get_item();
                    require 'item_card.php';
                }
                ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (Configuration::is_dev()): ?>
        <!-- TIME MANAGEMENT DEV -->
        <?php require 'time.php' ?>
    <?php endif; ?>

    <!-- NAVBAR MENU -->
    <?php require 'menu.php' ?>

</body>

</html>