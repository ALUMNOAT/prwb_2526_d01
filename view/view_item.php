<script>
    function changeMain(thumb) {
        document.getElementById('mainImage').src = thumb.src;
    }
</script>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($item->get_title()) ?></title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=account_circle,add,celebration,filter,gavel,home,schedule,settings,shopping_bag,travel_explore,trophy">
    <link rel="stylesheet" href="css/icons.css">
    <link rel="stylesheet" href="css/item.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

    <div class="item-detail">
        <h2 class="page-title">Item open</h2>
        <?php
        $mainPicPath = $mainPic ? $mainPic->get_picture_path() : null;
        ?>
        <?php /** @var Item $item */
        if ($item->get_main_picture_path()): ?>
            <p>
                <img id="mainImage" src="<?= $item->get_main_picture_path() ?>" alt="Item picture">
            </p>
        <?php else: ?>
            <img src="https://upload.wikimedia.org/wikipedia/commons/f/f3/Question_mark_alternate.png" />
        <?php endif; ?>

        <h1><?= htmlspecialchars($item->get_title()) ?></h1>
        <p class="desc"><?= htmlspecialchars($item->get_description() ?? "No description") ?></p>
        <p class="desc"><strong>Start:</strong> <?= htmlspecialchars($item->get_created_at()) ?></p>
        <p class="desc"><strong>Ends:</strong> <?= $item_status->get_end_at() ?></p>
    </div>

    <div class="images">
        <?php foreach ($pictures as $pic): ?>
            <img class="thumb" src="<?= htmlspecialchars($pic->get_picture_path()) ?>" onclick="changeMain(this)">
        <?php endforeach; ?>
    </div>

    <form method="POST" action="" class="form-box">
        <div class="price-container">
            <h3 class="desc">Pricing</h3>
            <?php
            $buyNowPrice = $item->get_buy_now_price() ?? 0;
            $currentBid = $currentBid ?? 0;
            ?>
            <label class="label"><strong>Current Bid:</strong>
                <?= $currentBid > 0 ? "€ " . number_format($currentBid, 2, ",", ".") : "N/A" ?>
            </label>
            <br>
            <label class="label"><strong>Buy Now:</strong>
                <?= $buyNowPrice > 0 ? "€ " . number_format($buyNowPrice, 2, ",", ".") : "N/A" ?>
            </label>
            <hr>

            <!-- owner of the item can't buy or bid himself on the item -->
            <?php if ($member->get_id() !== $item->get_owner()->get_id()): ?>
                <!-- Show bid input only if auction is enabled and if the item didn't got bought yet-->
                <?php if ($item_status->get_is_auction() && !$item_status->get_buy_now_reached()): ?>
                    €<input type="text" id="make_bid" name="make_bid" class="input" value="<?= $currentBid + 1 ?>">
                    <p><button type="submit" name="bid" class="bid">Place Bid</button></p>
                <?php endif; ?>

                <!-- Show Buy Now button only if Buy Now is enabled AND price > current bid -->
                <?php if ($item_status->get_has_buy_now() && $buyNowPrice > 0 && $buyNowPrice > $currentBid): ?>
                    <p><button type="submit" name="buy" class="buy">Buy now at € <?= $buyNowPrice ?></button></p>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </form>

    <div class="seller-container">
        <h3 class="desc">Seller Information</h3>
        <?php if (!$item->get_owner()->get_picture_path()): ?>
            <span class="material-symbols-outlined size-120">account_circle</span>
        <?php else: ?>
            <img src="<?= $item->get_owner()->get_picture_path() ?>" width="100" alt="<?= $member->get_pseudo() ?>&apos;s photo!">
        <?php endif; ?>
        <label class="label"><?= $item->get_owner()->get_pseudo() ?></label>
        <p class="desc">Member</p>
    </div>

    <div class="bid-history">
        <h3>Bid History</h3>

        <?php if (empty($bids)): ?>
            <p>No bids yet.</p>
        <?php else: ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>Bidder</th>
                        <th>Date</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bids as $bid): ?>
                        <tr <?= $bid->get_is_highest_bid() ? 'style="background-color:#dff0d8;"' : '' ?>>
                            <td><?= htmlspecialchars($bid->get_owner()->get_pseudo()) ?></td>
                            <td><?= htmlspecialchars($bid->get_created_at()) ?></td>
                            <td>
                                € <?= number_format($bid->get_amount(), 2, ",", ".") ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!--
O(1) comparaison avec ID vu qu'elles sont uniques
les object hidden peuvent encore etre manipulé par le client ...
donc pas de hidden
-->
    <?php if ($member->get_id() === $item->get_owner()->get_id()): ?>
        <div class="manage-item">
            <h3>Manage Your Item</h3>

            <a href="item/add_edit_item/<?= $item->get_id() ?>" class="row">
                <div class="row-title">Edit Item Details</div>
            </a>

            <a href="item/manage_images/<?= $item->get_id() ?>" class="row">
                <div class="row-title">Manage Images</div>
            </a>

            <?php /** @var ItemStatus $item_status */
            if (!$item_status->get_has_bids()): ?>
                <a href="my_items/delete/<?= $item->get_id() ?>" class="row">
                    <div class="row-title">Delete Item</div>
                </a>
            <?php endif; ?>
            
        </div>
    <?php endif; ?>

    <?php if (count($errors) != 0): ?>
        <div class='errors'>
            <p>Please correct the following error(s) :</p>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif (strlen($success) != 0): ?>
        <div class='success'>
            <?= $success ?>
        </div>
    <?php endif; ?>



</body>

</html>