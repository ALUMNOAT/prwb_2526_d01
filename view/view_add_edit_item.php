<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Item</title>
    <base href="<?= $web_root ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/add_edit_item.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="container">
    <h2 class="page-title">Add item</h2>

    <form method="POST" action="item/add_edit_item/<?= $item->get_id() ?>" class="form-box">

    <div class="section">
            <h3>Basic Information</h3>

            <label for="item_title" class="label">Item Title *</label>
            <input type="text" id="title" name="title" class="input" value="<?= $item?->get_title() ?? '' ?>" placeholder="Ex: iPhone 13 Pro Max 256GB" required>

            <label for="description" class="label">Description</label>
            <input type="text" id="description" name="description" class="input" value="<?= $item?->get_description() ?? '' ?>" placeholder="Describe your item in detail ..." required>

            <label for="days" class="label">Sale Duration (days) *</label>
            <input type="text" id="days" name="days" class="input" value="<?= $item?->get_duration_days() ?? '' ?>">
        </div>

        <div class="section">
            <h3>Sale Type</h3>

            <label for="starting_bid" class="label">Starting Bid</label>
            <input type="text" id="starting_bid" name="starting_bid" class="input" value="<?= $item?->get_starting_bid() ?? '' ?>" placeholder="e.g., 50.00">

            <label for="buy_now_price" class="label">Instant purchase Price <span class="secondary">(optional)</span></label>
            <input type="text" id="buy_now_price" name="buy_now_price" class="input" value="<?= $item?->get_buy_now_price() ?? '' ?>">

            <label for="sale_price" class="label">Sale Price</label>
            <input type="text" id="sale_price" name="sale_price" class="input" value="<?= ItemStatus::has_bids($item->get_id()) ?>">
        </div>

        <button type="submit" class="btn">Save</button>
    </form>

    <?php if(count($errors)!=0): ?>
        <div class='errors'>
            <p>Please correct the following error(s) :</p>
            <ul>
                <?php foreach($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

</div>
</body>
</html>