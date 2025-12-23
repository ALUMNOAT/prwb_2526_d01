<?php
/* 
doc:https: //www.w3schools.com/howto/howto_css_image_text.asp 
doc:https: //www.w3schools.com/icons/tryit.asp?filename=tryicons_google-collections
doc:https: //www.w3docs.com/snippets/css/how-to-make-a-div-a-clickable-link.html
doc:https://stackoverflow.com/questions/796087/make-a-div-into-a-link
*/

/** 
 * @var Item $item 
 * @var ItemStatus $item_status
 * @var array $my_bidded_items
 * @var array $available_items
 */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Browse</title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="css/browse.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="margin">
        <div>
            <h1>My Bids</h1>
        </div>

        <div class="browse">
            <?php foreach ($my_bidded_items as $item_status) {
                $item = $item_status->get_item();
                require 'item_card.php';
            } ?>
        </div>

        <div>
            <h1>Available Items</h1>
        </div>

        <div class="browse">
            <?php foreach ($available_items as $item_status) {
                $item = $item_status->get_item();
                require 'item_card.php';
            } ?>
        </div>
    </div>

    <?php require 'menu.php' ?>

</body>

</html>