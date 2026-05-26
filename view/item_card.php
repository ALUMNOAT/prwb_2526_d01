<?php
/*
doc: https://stackoverflow.com/questions/42623498/php-cast-to-my-class 
doc: https://stackoverflow.com/questions/22545325/how-to-vertically-align-text-with-icon-font
*/

/** 
 * @var Bid $bid
 * @var Item $item
 * @var ItemStatus $item_status 
 */
?>

<div class="card" data-description="<?= Tools::sanitize($item->get_description()) ?>" data-category="[<?= $item->get_categories_name() ?>]">
    <!-- CLICKABLE LINK -->
    <a href="item/show/<?= $item->get_id() ?>">
        <span class="link-card"></span>
    </a>

    <!-- IMAGE & LABEL -->
    <div class="container-image">
        <!-- IMAGE -->
        <?php if ($item->has_no_picture()): ?>
            <img src="https://upload.wikimedia.org/wikipedia/commons/f/f3/Question_mark_alternate.png"/>
        <?php else: ?>
            <img src="<?= $item->get_main_picture_path() ?>" alt="Avatar">
        <?php endif; ?>
        <!-- LABEL -->
        <div class="container-label">
            <!-- BIDDER & HIGHEST BIDDER -->
            <?php if (isset($bid)): ?>
                <?php if ($bid->get_is_highest_bid()): ?>
                    <div class="highest-bidder">
                        <span class="material-symbols-outlined size-20">trophy</span> Highest Bidder
                    </div>
                <?php else: ?>
                    <div class="bidder">
                        <span class="material-symbols-outlined size-20">gavel</span> Bidder
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="container-label-general-info">
                <!-- AUCTION -->
                <?php if ($item_status->get_is_auction()): ?>
                    <div class="auction">
                        <span class="material-symbols-outlined size-20">gavel</span> auction
                    </div>
                <?php endif; ?>

                <!-- BUY NOW -->
                <?php if ($item_status->get_has_buy_now()): ?>
                    <div class="buy-now">
                        <span class="material-symbols-outlined size-20">shopping_bag</span> buy now
                    </div>
                <?php endif; ?>

                <!-- IMAGE COUNT -->
                <?php if (count($item->get_pictures()) > 1): ?>
                    <div class="image-count">
                        <span class="material-symbols-outlined size-20">filter</span>
                        <?= count($item->get_pictures()) ?> images
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- TEXT -->
    <div class="container-text">
        <!-- TITLE & OWNER -->
        <p><b><?= Tools::sanitize($item->get_title()) ?></b></p>
        <p class="attribution">by <?= Tools::sanitize($item->get_owner()->get_full_name()) ?></p>

        <!-- PRICES -->
        <div class="container-prices">
            <!-- BUY NOW & STARTING BID -->
            <?php if ($item_status->get_has_buy_now()): ?>
                <div class="buy-now-price">
                    € <?= number_format($item->get_buy_now_price(), 2, ",", ".") ?>
                </div>
            <?php elseif ($item_status->get_is_auction()): //not necessary, but perhaps it's better practice 
            ?>
                <div class="starting-bid-price">
                    € <?= number_format($item->get_starting_bid(), 2, ",", ".") ?>
                </div>
            <?php endif; ?>

            <!-- MY BID -->
            <?php if (isset($bid)): ?>
                <div class="current-bid-price">
                    <p class="attribution">Current bid</p>
                    € <?= number_format($bid->get_highest_bid(), 2, ",", ".") ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- TIME LEFT -->
        <p class="green"><span class="material-symbols-outlined size-20">schedule</span> <?= $item_status->ends_in() ?></p>
    </div>
</div>