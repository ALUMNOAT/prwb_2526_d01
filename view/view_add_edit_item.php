<?php
$isEdit = $item !== null && $item->get_id() !== null;
$pageTitle = $isEdit ? "Edit Item" : "Add Item";

/**
 * @var array $categories
 *  */
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <base href="<?= $web_root ?>" />
    <!-- Bootstrap CSS (doit etre avant tout) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/colors.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=add,arrow_back,celebration,filter,gavel,home,save,schedule,settings,shopping_cart,travel_explore,trophy">
    <link rel="stylesheet" href="css/icons.css">
    <link rel="stylesheet" href="css/top_navbar.css">
    <link rel="stylesheet" href="css/add_edit_item.css" type="text/css" />
    <script src="lib/jquery-4.0.0.min.js" type="text/javascript"></script>
    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Modal -->
    <script src="scripts/add_edit_item_modal.js" type="text/javascript"></script>
    <script src="scripts/validations_(a)sync_edit_add_item.js" type="text/javascript"></script>
</head>

<body>
    <div class="top-navbar">
        <a href='profile'>
            <span class="material-symbols-outlined size-40">arrow_back</span>
        </a>

        <h2 class="page-title">
            <?= $pageTitle ?>
            <span class="material-symbols-outlined">shopping_cart</span>
        </h2>

        <button type="submit" form="add_item">
            <span class="material-symbols-outlined size-40">save</span>
        </button>
    </div>

    <div class="container">
        <form method="POST" name='add_item' id='add_item' action="" class="form-box">

            <div class="section">
                <h3>Basic Information</h3>

                <label for="item_title" class="label">Item Title *</label>
                <!-- Why Sanitize? Example
                <input value="MacBook Pro 14" M2 Pro"> (HTML breaks because of ")
                <input value="MacBook Pro 14&quot; M2 Pro"> (thanks to Tools::sanitize() and web changes it to ", HTML doesn't break)
                -->
                <input type="text" id="title" name="title" class="input"
                    value="<?= Tools::sanitize($item?->get_title() ?? '') ?>" placeholder="Ex: iPhone 13 Pro Max 256GB" required>
                <span id="error-title" class="error-message" style="color: red; display: none;"></span>

                <label for="description" class="label">Description</label>
                <input type="text" id="description" name="description" class="input"
                    value="<?= Tools::sanitize($item?->get_description() ?? '') ?>" placeholder="Describe your item in detail ...">
                <span id="error-description" class="error-message" style="color: red; display: none;"></span>

                <label for="days" class="label">Sale Duration (days) *</label>
                <input type="text" id="days" name="days" class="input" value="<?= $item?->get_duration_days() ?? '7' ?>">
                <span id="error-sale-duration" class="error-message" style="color: red; display: none;"></span>
            </div>

            <div class="section">
                <h3>Sale Type</h3>

                <?php
                // Determine values for the three inputs
                $startingBidVal = ($item?->get_starting_bid() ?? 0) > 0 ? $item->get_starting_bid() : '';
                // starting bid -> oui et buy_now_price -> oui
                $buyNowVal = ($item?->get_starting_bid() ?? 0) > 0 && ($item?->get_buy_now_price() ?? 0) > 0
                    ? $item->get_buy_now_price()
                    : '';
                // starting bid -> non et buy_now_price -> oui
                $salePriceVal = ($item?->get_starting_bid() ?? 0) <= 0 && ($item?->get_buy_now_price() ?? 0) > 0
                    ? $item->get_buy_now_price()
                    : '';
                ?>


                <!--  | Type              | starting_bid | buy_now_price | sale_price |
              | ----------------- | ------------ | ------------- | ---------- |
              | Auction only      | 50           |               |            |
              | Auction + Buy Now | 50           | 1500          |            |
              | Buy only          |              |               | 550        | -->



                <label for="starting_bid" class="label">Starting Bid</label>
                <input type="text" id="starting_bid" name="starting_bid" class="input" value="<?= $startingBidVal ?>" placeholder="e.g., 50.00">
                <span id="error-starting-bid" class="error-message" style="color: red; display: none;"></span>

                <label for="buy_now_price" class="label">Instant purchase Price <span class="secondary">(optional)</span></label>
                <input type="text" id="buy_now_price" name="buy_now_price" class="input" value="<?= $buyNowVal ?>">
                <span id="error-buy-now-price" class="error-message" style="color: red; display: none;"></span>

                <label for="sale_price" class="label">Sale Price</label>
                <input type="text" id="sale_price" name="sale_price" class="input" value="<?= $salePriceVal ?>">
                <span id="error-sale-price" class="error-message" style="color: red; display: none;"></span>

            </div>

            <div class="section">
                <h3>Categories</h3>
                <p>You can select up to 3 categories</p>


                <?php foreach($categories as $category): 
                    /** @var Category $category */ 
                    $unique_name = "category_" . $category->get_priority(); ?>
                    <input type="checkbox" id="<?= $unique_name ?>" name="<?= $unique_name ?>" value="<?= $category->get_name() ?>">
                    <label for="<?= $unique_name ?>"><?= $category->get_name() ?></label><br>
                <?php endforeach; ?>
            </div>
        </form>

        <?php if (count($errors) != 0): ?>
            <div class='errors'>
                <p>Please correct the following error(s) :</p>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

    </div>
</body>

</html>