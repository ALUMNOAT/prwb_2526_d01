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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse</title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="css/colors.css">
    <link rel="stylesheet" href="css/icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=<?= $icons ?>">
    <link rel="stylesheet" href="css/top_navbar.css">
    <link rel="stylesheet" href="css/manage_images.css">
    <link rel="stylesheet" href="css/menu.css">
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.14.2/jquery-ui.js"></script>
    <script>
        let middle_width, middle_height, item_id, photos;

        $(document).ready(function() {
            set_variables();
            remove_non_ajax();
            add_ajax();
            refresh_priority();
            make_it_sortable();
        });

        function make_it_sortable() {
            //https://stackoverflow.com/questions/2754021/jquery-get-a-list-of-values-of-an-attribute-from-elements-of-a-class <-- See Kobi's answer
            $("#sortable").sortable({
                opacity: 0.4,
                cursor: "move",
                cursorAt: { top: middle_height, left: middle_width },
                stop: function(event, ui) {
                    before_photos = photos;
                    refresh_priority();
                    priorities_before = before_photos.map(function(){return $(this).attr("data-priority");}).get();
                    priorities_after = photos.map(function(){return $(this).attr("data-priority");}).get();
                    db_update_priorities(priorities_before, priorities_after);
                }
            });
        }

        function set_variables() {
            //https://stackoverflow.com/questions/5873810/how-can-i-get-last-characters-of-a-string <-- see Jamon answer's
            //https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/parseInt
            //https://api.jquery.com/children/
            //https://html.spec.whatwg.org/#embedding-custom-non-visible-data-with-the-data-*-attributes
            //https://www.tutorialrepublic.com/faq/how-to-get-the-children-of-the-this-selector-in-jquery.php
            middle_width = $("#sortable div:first").width()/2;
            middle_height = $("#sortable div:first img").height()/2;
            item_id = parseInt($("#input_images").attr("action").split("/").pop()); //Jamon + parseInt ^

            photos = $("#sortable").children().each(function() {
                priority = parseInt($(this).find("button[name='delete']").attr("value"));
                $(this).attr("data-priority", priority);
                $(this).find("button[name='move']").attr("data-priority", priority);
            });
        }

        function remove_non_ajax() {
            //https://api.jquery.com/removeAttr/
            $("#move_image").remove();
            $("#remove_image").remove();
            $("#sortable button").removeAttr("formaction type form")
        }

        function add_ajax() {
            //https://api.jquery.com/attribute-equals-selector/
            //https://stackoverflow.com/questions/9122078/difference-between-onclick-vs-click
            $("#sortable button[name='move']").on("click", function() {
                move_image($(this).attr("data-priority"), $(this).attr("value"))
            });
            $("#sortable button[name='delete']").on("click", function() {delete_image()});
        }

        function refresh_priority() {
            photos = $("#sortable").children();

            i = 1;
            photos.each(function() {
                $(this).attr("data-priority", i++);
                $(this).find("button[name='move']").attr("data-priority", priority);
            })
        }

        function delete_image() {
            //TODO: add ajax
            console.log("it works");
        }

        function move_image(from_priority, to_priority) {
            //TODO: add ajax
            console.log(from_priority + " -> " + to_priority);
            $.post("item/move_image_ajax/"+item_id,
                {source_priority: from_priority.toString(), target_priority: to_priority.toString() }
            )
        }

        function db_update_priorities(priorities_before, priorities_after) {
            //TODO: add ajax
            console.log(priorities_before + " -> " + priorities_after);
        }

  </script>
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

            <form id="input_images" method="POST" action="item/add_images/<?= $item->get_id() ?>" enctype="multipart/form-data">
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

            <h2>Current Image<?= $item->has_single_picture() ? "" : "s" ?></h2>

            <form method="POST" id="remove_image" action="item/remove_image/<?= $item->get_id() ?>"></form>

            <form method="POST" id="move_image" action="item/move_image/<?= $item->get_id() ?>"></form>

            <?php $image_count = $item->get_picture_count(); ?>

            <div class="images_container" id="sortable">
                <?php foreach ($item->get_pictures() as $item_picture): ?>
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