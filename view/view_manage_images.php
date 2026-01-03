<?php
/*
doc: https://stackoverflow.com/questions/3828554/how-to-allow-input-type-file-to-accept-only-image-files
doc: https://www.w3schools.com/tags/att_input_multiple.asp
*/

/**
 * @var Item $item
 * @var ItemPicture $item_picture
 */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Browse</title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=add,celebration,filter,gavel,home,schedule,settings,shopping_bag,travel_explore,trophy" />
    <link rel="stylesheet" href="css/manage_images.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div>
        <h1>Manage Images for "<?= $item->get_title() ?>"</h1>
        <div>
            <h2>Add New Images</h2>
            <form method="POST" action="item/add_images">
                <label for="images">Select Images</label><br>
                <input type="file" placeholder="Choose Files" id="images" name="images" multiple>
                <p>You can select multiple images (JPG, PNG, GIF, WebP). Images will be added to the end of the current list.</p>
                <input type="submit">
            </form>
        </div>
        <div>
            <h2>Current Image<?= $item->has_single_picture() ? "" : "s" ?></h2>
            <form method="POST" id="remove_image" action="item/remove_image/<?= $item->get_id() ?>"></form>
            <form method="POST" id="move_image_up" action="item/move_image_up/<?= $item->get_id() ?>"></form>
            <form method="POST" id="move_image_down" action="item/move_image_down/<?= $item->get_id() ?>"></form>
            <?php foreach ($item->get_pictures() as $item_picture): ?>
                <?php /** @var ItemPicture $item_picture */
                $priority = $item_picture->get_priority() ?>
                <img src="<?= $item_picture->get_thumbnail_path() ?>">
                <input type="hidden" form="remove_image" name="priority" value="<?= $priority ?>">
                <input type="hidden" name="item_id" form="move_image_up" value="<?= $priority ?>">
                <input type="hidden" name="item_id" form="move_image_down" value="<?= $priority ?>">
                <input type="submit" form="remove_image" value="X">
                <input type="submit" form="move_image_up" value="<<">
                <input type="submit" form="move_image_down" value=">>">
            <?php endforeach; ?>

        </div>
    </div>
</body>