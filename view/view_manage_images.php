<?php
/*
doc: https://stackoverflow.com/questions/3828554/how-to-allow-input-type-file-to-accept-only-image-files
doc: https://www.w3schools.com/tags/att_input_multiple.asp
doc: https://github.com/bigskysoftware/htmx/issues/1541
doc: https://www.w3.org/wiki/Common_HTML_entities_used_for_typography
doc: https://stackoverflow.com/questions/43267778/how-to-submit-multiple-values-in-a-single-html-form
doc: https://stackoverflow.com/questions/10652617/change-form-action-target-without-javascript
doc: https://stackoverflow.com/questions/4726809/send-both-post-and-get-in-a-form
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
            <?php //doc: https://github.com/bigskysoftware/htmx/issues/1541 ?>
            <h2>Current Image<?= $item->has_single_picture() ? "" : "s" ?></h2>
            <form method="POST" id="remove_image" action="item/remove_image/<?= $item->get_id() ?>"></form>
            <form method="POST" id="move_image" action="item/move_image/<?= $item->get_id() ?>"></form>
            <?php $image_count = $item->get_picture_count(); ?>
            <?php foreach ($item->get_pictures() as $item_picture): ?>
                <?php /** @var ItemPicture $item_picture */
                $priority = $item_picture->get_priority() ?>
                
                <img src="<?= $item_picture->get_thumbnail_path() ?>">
                <button type="submit" name="priority" form="remove_image" value="<?= $priority ?>">X</button>
                <button type="submit" name="priority" form="move_image" value="<?= $priority ?>,<?= ($priority-1) ?>" <?= $priority==1 ? "disabled" : "" ?>>&laquo;</button>
                <button type="submit" name="priority" form="move_image" value="<?= $priority ?>,<?= ($priority+1) ?>" <?= $priority==$image_count ? "disabled" : "" ?>>&raquo;</button>
            <?php endforeach; ?>

        </div>
    </div>
</body>