<?php
$isEdit = $item !== null && $item->get_id() !== null;
$pageTitle = $isEdit ? "Edit Item" : "Add Item";
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <base href="<?= $web_root ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/add_edit_item.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="container">
        <h2 class="page-title"><?= $pageTitle ?></h2>

        <form method="POST" action="" class="form-box">

            <div class="section">
                <h3>Basic Information</h3>

                <label for="item_title" class="label">Item Title *</label>
                <!-- Why Sanitize? Example
                <input value="MacBook Pro 14" M2 Pro"> (HTML breaks because of ")
                <input value="MacBook Pro 14&quot; M2 Pro"> (thanks to Tools::sanitize() and web changes it to ", HTML doesn't break)
                -->
                <input type="text" id="title" name="title" class="input" value="<?= Tools::sanitize($item?->get_title() ?? '' )?>" placeholder="Ex: iPhone 13 Pro Max 256GB" required>

                <label for="description" class="label">Description</label>
                <input type="text" id="description" name="description" class="input" value="<?= Tools::sanitize($item?->get_description() ?? '' )?>" placeholder="Describe your item in detail ..." required>

                <label for="days" class="label">Sale Duration (days) *</label>
                <input type="text" id="days" name="days" class="input" value="<?= $item?->get_duration_days() ?? '7' ?>">
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

                <label for="buy_now_price" class="label">Instant purchase Price <span class="secondary">(optional)</span></label>
                <input type="text" id="buy_now_price" name="buy_now_price" class="input" value="<?= $buyNowVal ?>">

                <label for="sale_price" class="label">Sale Price</label>
                <input type="text" id="sale_price" name="sale_price" class="input" value="<?= $salePriceVal ?>">

            </div>

            <button type="submit" class="btn">Save</button>
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