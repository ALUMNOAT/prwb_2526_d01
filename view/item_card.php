<?php
/*
doc: https://stackoverflow.com/questions/42623498/php-cast-to-my-class 
*/

/** 
 * @var Bid $bid
 * @var Item $item
 * @var ItemStatus $item_status 
 */
?>

<div class="card">
    <a href="item/<?= $item->get_id() ?>">
        <span class="link-card"></span>
    </a>

    <div class="container_image">
        <img src="<?= $item->get_main_picture_path() ?>" alt="Avatar" style="width:100%">

        <div class="container-info">
            <?php if (isset($bid)): ?>
                <div class="top-left">
                    <span class="material-symbols-outlined">
                        <?= $bid->get_is_highest_bid()
                            ? 'trophy'
                            : 'gavel'
                        ?>
                    </span>
                    <?= $bid->get_is_highest_bid()
                        ? 'Highest Bidder'
                        : 'Bidder'
                    ?>
                </div>
            <?php endif; ?>

            <div class="top-right">
                Auction
            </div>

            <?php if (false): ?>
                <div class="centered">
                    Centered
                </div>
            <?php endif; ?>

            <?php if (count($item->get_pictures()) > 1): ?>
                <div class="bottom-right">
                    <i class="material-icons md-18">collections</i><?= count($item->get_pictures()) ?> images
                </div>
            <?php endif; ?>

            <?php if (false): ?>
                <div class="bottom-left">
                    Bottom Left
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <h4><b><?= $item->get_title() ?></b></h4>
        <p class="attribution">by <?= $item->get_owner()->get_full_name() ?></p>
        <p><?= $item_status->ends_in() ?></p>
    </div>
</div>