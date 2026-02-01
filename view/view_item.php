<?php

/** 
 * @var ItemStatus $item_status 
 * @var Item $item 
 * @var Member $member
 * @var ItemPicture $main_pic
 * */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $item->get_title() ?></title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=account_circle,add,celebration,filter,gavel,home,schedule,settings,shopping_bag,travel_explore,trophy">
    <link rel="stylesheet" href="css/icons.css">
    <link rel="stylesheet" href="css/item.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

    <!--ITEM DETAILS-->
    <div class="item-detail">
        <h2 class="page-title">Item open</h2>
        <?php if ($item->has_no_picture()): ?>
            <img src="https://upload.wikimedia.org/wikipedia/commons/f/f3/Question_mark_alternate.png" />
        <?php else: ?>
            <img src="<?= $main_pic->get_picture_path() ?>" alt="Item picture">
        <?php endif; ?>

        <h1><?= $item->get_title() ?></h1>
        <p class="desc"><?= $item->get_description() ?? "No description" ?></p>
        <p class="desc"><strong>Start:</strong> <?= $item->get_created_at() ?></p>
        <p class="desc"><strong>Ends:</strong> <?= $item_status->get_end_at() ?></p>
    </div>

    <!-- THUMBNAILS -->
    <div class="images">
        <?php /** @var ItemPicture $pic */
        foreach ($pictures as $pic): ?>
            <a href="item/show/<?= $item->get_id() ?>/<?= $pic->get_priority() ?>">
                <img class="thumb" src="<?= $pic->get_picture_path() ?>">
            </a>
        <?php endforeach; ?>
    </div>

    <!-- BIDS/BUY -->
    <!-- <form method="POST" action="" class="form-box">
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

            <hr> -->

    <!-- owner of the item can't buy or bid himself on the item -->
    <?php if ($member->get_id() !== $item->get_owner()->get_id()): ?>
        <!-- Show bid input only if auction is enabled and if the item didn't got bought yet-->
        <?php if ($item_status->get_is_auction() && !$item_status->get_buy_now_reached()): ?>
            <!-- €<input type="text" id="make_bid" name="make_bid" class="input" value="<?= $currentBid + 1 ?>">
                    <p><button type="submit" name="bid" class="bid">Place Bid</button></p> -->
        <?php endif; ?>

        <!-- Show Buy Now button only if Buy Now is enabled AND price > current bid -->
        <?php if ($item_status->get_has_buy_now() && $buyNowPrice > 0 && $buyNowPrice > $currentBid): ?>
            <!-- <p><button type="submit" name="buy" class="buy">Buy now at € <?= $buyNowPrice ?></button></p> -->
        <?php endif; ?>
    <?php endif; ?>

    <!-- </div>
    </form> -->

    <!-- BIDS/BUY -->
    <div class="price-container">
        <h3 class="desc">Pricing 2</h3>

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

        <!-- is the item biddable (by the connected member) ? -->
        <?php if ($item_status->is_biddable($member)): ?>
            <form method="POST" action="item/bid/<?= $item->get_id() ?>/<?= $main_pic->get_priority() ?>" class="form-box">
                €<input type="text" id="bid_amount" name="bid_amount" class="input" value="<?= $currentBid + 1 ?>">
                <br>
                <br>
                <button type="submit">Place Bid</button>
            </form>
        <?php endif; ?>

        <!-- is the item buyable (by the connected member) ? -->
        <?php if ($item_status->is_buyable($member)): ?>
            <form method="POST" action="item/buy/<?= $item->get_id() ?>/<?= $main_pic->get_priority() ?>" class="form-box">
                <button type="submit" name="buy" class="buy">Buy now at € <?= $buyNowPrice ?></button>
            </form>
        <?php endif; ?>
    </div>

    <!-- SELLER INFO -->
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

    <!-- BIDS HISTORY -->
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
                            <td><?= $bid->get_owner()->get_pseudo() ?></td>
                            <td><?= $bid->get_created_at() ?></td>
                            <td>
                                € <?= number_format($bid->get_amount(), 2, ",", ".") ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>


    <?php if ($item->is_owner($member)): ?>
        <!-- OWNER'S ACTIONS -->
        <div class="manage-item">
            <h3>Manage Your Item</h3>

            <a href="item/edit/<?= $item->get_id() ?>" class="row">
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

    <!-- ERRORS -->
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