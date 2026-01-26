<?php
/* 
doc: https: //www.w3schools.com/howto/howto_css_image_text.asp 
doc: https: //www.w3schools.com/icons/tryit.asp?filename=tryicons_google-collections
doc: https: //www.w3docs.com/snippets/css/how-to-make-a-div-a-clickable-link.html
doc: https://stackoverflow.com/questions/796087/make-a-div-into-a-link
doc: https://developers.google.com/fonts/docs/material_symbols#getting_material_symbols
doc: https://stackoverflow.com/questions/45941415/left-align-one-element-and-center-another-element-in-div

problem not fixed: https://github.com/google/material-design-icons/issues/1834

ATTENTION: Google's Material Symbols are fiddly to deal with, so watch out !

doc: https://www.w3schools.com/css/css3_pagination.asp
*/

/** 
 * @var Member $user
 * @var Member $loyal
 * @var Item $item
 * @var ItemStatus $item_status
 * @var Bid $bid
 * @var array $transactions
 * @var array $loyals
 * @var string $transactions_type
 * @var float $total
 * @var float $average
 * @var int $sales_count
 */

$is_sales_view = $transactions_type == 'sales';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Browse</title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=add,arrow_back,attach_money,celebration,filter,gavel,home,schedule,settings,shopping_bag,shopping_cart,shoppingmode,travel_explore,trophy">
    <link rel="stylesheet" href="css/transactions.css">
    <link rel="stylesheet" href="css/top_navbar.css">
    <link rel="stylesheet" href="css/icons.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/time.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="top-navbar">
        <a href='profile'>
            <span class="material-symbols-outlined size-40">arrow_back</span>
        </a>

        <h2 class="page-title">
            <?= ucfirst($transactions_type) ?>
            <span class="material-symbols-outlined">shopping_cart</span>
        </h2>
    </div>

    <div class='content'>
        <div class="flex-row">
            <div>
                <h1><?= $is_sales_view ? 'Completed Sales' : 'My Purchases' ?></h1>
                <p class='attribution'>
                    <?= $is_sales_view
                        ? "A snapshot of the deals you've wrapped up."
                        : "Track the gear you've succesfully secured." ?>
                </p>
            </div>

            <div>
                <p class='count <?= $transactions_type ?>'>
                    <?= $transactions_count ?> <?= $transactions_type ?>
                </p>
            </div>
        </div>

        <div class="data">
            <div>
                <h3><?= $is_sales_view ? 'TOTAL REVENUE' : 'TOTAL SPENT' ?></h3>
                <h2>€ <?= $total ?></h2>
                <p class="attribution">Across <?= $transactions_count . ' ' . $transactions_type ?></p>
            </div>

            <div>
                <h3>AVERAGE TICKET</h3>
                <h2>€ <?= $average ?></h2>
                <p class="attribution">
                    <?= $is_sales_view
                        ? 'Median buyer appetite indicator'
                        : 'Helps you plan future bids' ?>
                </p>
            </div>

            <div>
                <h3><?= $is_sales_view ? 'LOYAL BIDDER(s)' : 'TOP SELLER(s)' ?></h3>

                <h2>
                    <?php foreach ($loyals as $loyal):
                        /** @var Member $loyal */
                    ?>
                        <?= $loyal->get_pseudo() ?>
                    <?php endforeach; ?>
                </h2>

                <p class="attribution">
                    <?= $is_sales_view
                        ? 'most recurring winning buyer(s)'
                        : "Who you've bought from the most" ?>
                </p>
            </div>

        </div>

        <!-- DATA -->
        <div>

        </div>

        <!-- SALES -->
        <?php if (!empty($transactions)): ?>
            <div class="browse">
                <?php foreach ($transactions as $a_bid):
                    /** @var Bid $a_bid */
                    if (!$is_sales_view) {
                        /** @var Bid $bid */
                        $bid = $a_bid;
                        $bid->set_highest_bid($bid->get_amount()); //ugly but works...
                    }

                    $item_status = $a_bid->get_item_status();
                    $item = $item_status->get_item();
                ?>
                    <div>
                        <?php require 'item_card.php';
                        /** 
                         * @var Bid $a_bid 
                         * @var ItemStatus $item_status */
                        ?>

                        <div class='anecdote'>
                            <?php if ($is_sales_view): ?>
                                <p>
                                    <span class='material-symbols-outlined'>attach_money</span>
                                    Final price € <?= NumberConverter::money_format($a_bid->get_amount()) ?>
                                </p>

                                <p>
                                    <span class='material-symbols-outlined'>trophy</span>
                                    <?= $a_bid->get_owner()->get_pseudo() ?>
                                </p>

                                <p>
                                    <span class='material-symbols-outlined'>schedule</span>
                                    Closed on <?= $item_status->get_buy_now_reached() ? $a_bid->get_created_at() : $item_status->get_end_at() ?>
                                </p>
                            <?php else: ?>
                                <p>
                                    <span class='material-symbols-outlined icon-flipped'>shoppingmode</span>
                                    Paid € <?= NumberConverter::money_format($a_bid->get_amount()) ?>
                                </p>
                                <p>
                                    <span class='material-symbols-outlined'>schedule</span>
                                    Closed on <?= $item_status->get_buy_now_reached() ? $a_bid->get_created_at() : $item_status->get_end_at() ?>
                                </p>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (Configuration::is_dev()): ?>
        <!-- TIME MANAGEMENT DEV -->
        <?php require 'time.php' ?>
    <?php endif; ?>

    <!-- NAVBAR MENU -->
    <?php require 'menu.php' ?>

</body>

</html>