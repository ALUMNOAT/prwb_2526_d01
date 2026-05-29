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
 * @var Bid $bid
 * @var Item $item
 * @var ItemStatus $item_status
 * @var array $my_bids
 * @var array $available_items
 * @var int $page
 * @var int $my_bids_pagination
 * @var int $available_items_pagination
 * @var int $pagination
 */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Browse</title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="css/colors.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=add,celebration,filter,gavel,home,schedule,settings,shopping_bag,travel_explore,trophy">
    <link rel="stylesheet" href="css/icons.css">
    <link rel="stylesheet" href="css/browse.css">
    <link rel="stylesheet" href="css/item_card.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/time.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="lib/jquery-4.0.0.min.js" type="text/javascript"></script>
    <script src="scripts/recherche_fulltext.js" type="text/javascript"></script>
    <script src="scripts/search_propagation.js" type="text/javascript"></script>
</head>

<body>
    <div class="search-bar only-js">

    <!-- Champ texte où l'utilisateur tape sa recherche -->
      <input type="text" id="fulltext-search" placeholder="Search..." autocomplete="off">

      <!-- Menu déroulant pour choisir sur quel champ filtrer -->
      <select id="search-field">
        <option value="all">All</option>
        <option value="title">Title</option>
        <option value="owner">Owner</option>
        <option value="description">Description</option>
      </select>

      <?php 
      $allCategories = Categories::get_all_categories();
      ?>
      <!-- Menu déroulant pour les catégories -->
      <select id="category-field">
        <option value="all">All Categories</option>
        <?php foreach ($allCategories as $categoryName) : 
            $name = $categoryName->get_name()?>
            <option value="<?= $name ?>"><?= $name ?></option>
        <?php endforeach ?>
      </select> 

    </div>

    <div class="content">
        <!-- MY BIDS -->
        <?php if ($user->get_id() !== null && !empty($my_bids)): ?>

            <h1>My Bids</h1>
            <div class="browse">
                <?php foreach ($my_bids as $bid) {
                    $item_status = $bid->get_item_status();
                    $item = $item_status->get_item();
                    require 'item_card.php';
                }

                unset($bid); //necessary otherwise the last bid will show in available items (weird !)
                ?>
            </div>

            <!-- PAGINATION -->
            <?php
            $pagination = $my_bids_pagination;
            require 'item_pagination.php'
            ?>

        <?php endif; ?>

        <!-- AVAILABLE ITEMS -->
        <?php if (!empty($available_items)): ?>

            <h1>Available Items</h1>
            <div class="browse">
                <?php foreach ($available_items as $item_status) {
                    $item = $item_status->get_item();
                    require 'item_card.php';
                } ?>
            </div>

            <!-- PAGINATION -->
            <?php
            $pagination = $available_items_pagination;
            require 'item_pagination.php'
            ?>

        <?php endif; ?>
    </div>

    <!-- NO AVAILABLE ITEMS -->
    <?php if (empty($my_bids) && empty($available_items)): ?>
        <div>
            <p>Nothing here but chickens !</p>
        </div>
    <?php endif; ?>

    <?php if (Configuration::is_dev()): ?>
        <!-- TIME MANAGEMENT DEV -->
        <?php require 'time.php' ?>
    <?php endif; ?>

    <!-- NAVBAR MENU -->
    <?php require 'menu.php' ?>

    <!-- On ajoute la classe js-enabled au body, comme ca pas besoin de s'amuser a changer chaque bouton -->
    <script>document.body.classList.add('js-enabled');</script>

</body>

</html>