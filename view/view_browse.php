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
    <!--
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">
    <link rel="stykesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&icon_names=filter,gavel,trophy&display=block">
    <link rel="stykesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&icon_names=home,palette,settings&display=block">
    -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=filter,gavel,trophy" />
    <link rel="stylesheet" href="css/browse.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="margin">

        <?php if ($user->get_id() !== null): ?>
            <h1>My Bids</h1>
            <div class="browse">
                <?php foreach ($my_bids as $bid) {
                    $item_status = $bid->get_item_status();
                    $item = $item_status->get_item();
                    require 'item_card.php';
                } ?>
            </div>
        <?php endif; ?>

        <h1>Available Items</h1>
        <div class="browse">
            <?php foreach ($available_items as $item_status) {
                $item = $item_status->get_item();
                unset($bid); //necessary otherwise the last bid will show in available items (weird !)
                require 'item_card.php';
            } ?>
        </div>

        <p><a href='placeholder/logout_user'>Logout</a>
    </div>



    <?php require 'menu.php' ?>

</body>

</html>