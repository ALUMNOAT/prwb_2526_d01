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

$icons = "arrow_left_alt,arrow_right_alt,close_small";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Browse</title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=<?= $icons ?>" />
    <link rel="stylesheet" href="css/manage_images.css">
    <link rel="stylesheet" href="css/menu.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="content">
        <h1>Manage Images for "<?= $item->get_title() ?>"</h1>

        <div class="section">
            <h2>Add New Images</h2>
            <form method="POST" action="item/add_images">
                <h3><b>Select Images</b></h3>
                <input class="like_button" type="file" placeholder="Choose Files" id="images" name="images" multiple>
                <br>
                <label class="test" for="images">You can select multiple images (JPG, PNG, GIF, WebP). Images will be added to the end of the current list.</label>
                <br>
                <button class="upload" type="submit">Upload Images</button>
            </form>
        </div>

        <div class="section">
            <?php //doc: https://github.com/bigskysoftware/htmx/issues/1541 
            ?>
            <h2>Current Image<?= $item->has_single_picture() ? "" : "s" ?></h2>
            <form method="POST" id="remove_image" action="item/remove_image/<?= $item->get_id() ?>"></form>
            <form method="POST" id="move_image" action="item/move_image/<?= $item->get_id() ?>"></form>
            <?php $image_count = $item->get_picture_count(); ?>
            <div class="images_container">
                <?php foreach ($item->get_pictures() as $item_picture): ?>
                    <div>
                        <?php /** @var ItemPicture $item_picture */
                        $priority = $item_picture->get_priority() ?>
                        <img src="<?= $item_picture->get_thumbnail_path() ?>">
                        <br>
                        <button type="submit" name="priority" form="move_image" value="<?= $priority ?>,<?= ($priority - 1) ?>" <?= $priority == 1 ? "disabled" : "" ?>>
                            <span class="material-symbols-outlined">arrow_left_alt</span>
                        </button>
                        <button type="submit" name="priority" form="move_image" value="<?= $priority ?>,<?= ($priority + 1) ?>" <?= $priority == $image_count ? "disabled" : "" ?>>
                            <span class="material-symbols-outlined">arrow_right_alt</span>
                        </button>
                        <button class="delete" type="submit" name="priority" form="remove_image" value="<?= $priority ?>">
                            <span class="material-symbols-outlined">close_small</span>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- NAVBAR MENU -->
    <?php require 'menu.php' ?>
</body>