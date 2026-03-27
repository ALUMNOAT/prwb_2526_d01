<?php
/*
doc: https://stackoverflow.com/questions/3828554/how-to-allow-input-type-file-to-accept-only-image-files
doc: https://www.w3schools.com/tags/att_input_multiple.asp
doc: https://github.com/bigskysoftware/htmx/issues/1541
doc: https://www.w3.org/wiki/Common_HTML_entities_used_for_typography
doc: https://stackoverflow.com/questions/43267778/how-to-submit-multiple-values-in-a-single-html-form
doc: https://stackoverflow.com/questions/10652617/change-form-action-target-without-javascript
doc: https://stackoverflow.com/questions/4726809/send-both-post-and-get-in-a-form
doc: https://www.w3schools.com/TAGs/att_form_enctype.asp
*/

/**
 * @var Item $item
 * @var ItemPicture $item_picture
 * @var array $errors
 */

$icons = "add,arrow_back,arrow_left_alt,arrow_right_alt,celebration,close_small,filter,home,schedule,settings,shopping_bag,shopping_cart,travel_explore,trophy";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Browse</title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="css/colors.css">
    <link rel="stylesheet" href="css/icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=<?= $icons ?>">
    <link rel="stylesheet" href="css/top_navbar.css">
    <link rel="stylesheet" href="css/manage_images.css">
    <link rel="stylesheet" href="css/menu.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="top-navbar">
        <a href='profile'>
            <span class="material-symbols-outlined size-40">arrow_back</span>
        </a>

        <h2 class="page-title">
            Manage Images
            <span class="material-symbols-outlined">shopping_cart</span>
        </h2>
    </div>

    <div class="content">
        <h1>Manage Images for "<?= Tools::sanitize($item->get_title()) ?>"</h1>

        <div class="section">
            <h2>Add New Images</h2>

            <form method="POST" action="item/add_images/<?= $item->get_id() ?>" enctype="multipart/form-data">
                <h3>
                    <b>Select Images</b>
                </h3>

                <input class="like_button" type="file" id="images" name="images[]" placeholder="Choose Files" accept="image/jpg, image/jpeg, image/x-png, image/gif, image/webp" multiple>

                <label class="test" for="images">You can select multiple images (JPG, PNG, GIF, WebP). Images will be added to the end of the current list.</label>

                <br>

                <button class="upload" id="upload" name="upload" type="submit">Upload Images</button>
            </form>

        </div>


        <!-- ERRORS -->
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class='errors'>
                <p>The following files cannot be uploaded:</p>
                <ul>
                    <?php foreach ($errors as $name_key => $errors_details): ?>
                        <li><?= $name_key ?> :
                            <ul>
                                <?php foreach ($errors_details as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>


        <div class="section">
            <?php //doc: https://github.com/bigskysoftware/htmx/issues/1541
            ?>

            <h2>Current Image<?= $item->get_pictures_()->has_single_picture() ? "" : "s" ?></h2>

            <form method="POST" id="remove_image" action="item/remove_image/<?= $item->get_id() ?>"></form>

            <form method="POST" id="move_image" action="item/move_image/<?= $item->get_id() ?>"></form>

            <?php $image_count = $item->get_pictures_()->get_picture_count(); ?>

            <div class="images_container">
                <?php foreach ($item->get_pictures_()->get as $item_picture): ?>
                    <div>
                        <?php /** @var ItemPicture $item_picture */ ?>
                        <?php $priority = $item_picture->get_priority() ?>

                        <img src="<?= $item_picture->get_thumbnail_path() ?>">

                        <br>

                        <?php if (!$item_picture->is_first_priority()): ?>
                            <button formaction="item/move_image/<?= $item->get_id() ?>/<?= $priority ?>" 
                            type="submit" name="move" id='move' form="move_image" value="<?= ($priority-1) ?>">
                                <span class="material-symbols-outlined">arrow_left_alt</span>
                            </button>
                        <?php endif; ?>

                        <?php if (!$item_picture->is_last_priority()): ?>
                            <button formaction="item/move_image/<?= $item->get_id() ?>/<?= $priority ?>" 
                            type="submit" name="move" id='move' form="move_image" value="<?= ($priority+1) ?>">
                                <span class="material-symbols-outlined">arrow_right_alt</span>
                            </button>
                        <?php endif; ?>

                        <button class="delete" type="submit" name="delete" id='delete' form="remove_image" value="<?= $priority ?>">
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