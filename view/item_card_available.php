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

<div class="card">
    <!-- CLICKABLE LINK -->
    <a href="item/<?= $item->get_id() ?>">
        <span class="link-card"></span>
    </a>

    <!-- IMAGE -->
    <div class="container-image">
        <img src="<?= $item->get_main_picture_path() ?>" alt="Avatar">

        <!-- LABEL -->
        <div class="container-label">
            <div class="container-label-general-info">
                <?php if ($item_status->get_is_auction()): ?>
                    <div class="auction">
                        <span class="material-symbols-outlined">gavel</span> auction
                    </div>
                <?php endif; ?>

                <?php if ($item_status->get_has_buy_now()): ?>
                    <div class="buy-now">
                        <span class="material-symbols-outlined">shopping_bag</span> buy now
                    </div>
                <?php endif; ?>

                <?php if (count($item->get_pictures()) > 1): ?>
                    <div class="image-count">
                        <span class="material-symbols-outlined">filter</span>
                        <?= count($item->get_pictures()) ?> images
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- TEXT -->
    <div class="container-text">
        <!-- TITLE & OWNER -->
        <h4><b><?= $item->get_title() . $item_status->get_end_at() ?></b></h4>
        <p class="attribution">by <?= $item->get_owner()->get_full_name() ?></p>

        <!-- PRICES -->
        <div class="container-prices">
            <?php if ($item_status->get_has_buy_now()): ?>
                <div class="buy-now-price">
                    € <?= number_format($item->get_buy_now_price(), 2,",", ".") ?>
                </div>
            <?php else: ?>
                <div class="starting-bid-price">
                    € <?= number_format($item->get_starting_bid(), 2,",", ".") ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- TIME LEFT -->
        <p class="green"><span class="material-symbols-outlined">schedule</span> <?= $item_status->ends_in() ?></p>
    </div>
</div>