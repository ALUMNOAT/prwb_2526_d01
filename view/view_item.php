<?php

/** 
 * @var ItemStatus $item_status 
 * @var Item $item 
 * @var Member $member
 * @var ItemPicture $main_pic
 * @var string $bid_path
 * @var string $buy_path
 * */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= Tools::sanitize($item->get_title()) ?></title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="css/colors.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=account_circle,add,arrow_back,celebration,filter,gavel,home,schedule,settings,shopping_bag,shopping_cart,travel_explore,trophy">
    <link rel="stylesheet" href="css/icons.css">
    <link rel="stylesheet" href="css/top_navbar.css">
    <link rel="stylesheet" href="css/item.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script>
        let images, item_id;

        $(document).ready(function() {
            set_variables();
            set_images();
            console.log("test");
            set_carousel();
        });

        function set_variables() {
            item_id = parseInt($(".item-data").attr("data-item_id"));
        }

        async function set_images() {
            await $.get(
                "item/get_images_service/"+item_id,
                function(data) {
                    //let images = data["responseJSON"]["data"];
                    console.log(data["message"]);
                    console.log(data["data"]);
                },
                "json" // this tells use the response will be JSON, no need to $.parseJSON(data) therefore !
            ).fail(function(data) {
                console.log("failed to load images");
                // let response = data["responseJSON"];
                // $("#image_error").attr("class", "errors");
                // let error_message = "<b>" + data["status"] + " " + data["statusText"] + ": " +"</b>" + response["error"];
                // $("#image_error").append(error_message);
            });
        }

        async function set_carousel() {
            //https://api.jquery.com/prepend/#:~:text=The%20.,append()%20).
            img_carousel = $(".img").empty();
            img_carousel.attr("id", "carousel").addClass("carousel slide");
            img_carousel.append("<");

            console.log("test2");
        }
    </script>
</head>

<body>

    <div id="carouselExampleIndicators" class="carousel slide">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="https://www.dummyimage.com/600x400/000/fff&text=hello_world" class="d-block w-100" alt="...">
            </div>
            <div class="carousel-item">
                <img src="https://www.dummyimage.com/600x400/000/fff&text=WOWOW" class="d-block w-100" alt="...">
            </div>
            <div class="carousel-item">
                <img src="https://www.dummyimage.com/600x400/000/fff&text=alright" class="d-block w-100" alt="...">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- TOP NAVBAR -->
    <div class="top-navbar">
        <a href="item/browse">
            <span class="material-symbols-outlined size-40">arrow_back</span>
        </a>

        <h2 class="page-title">
            Item open
            <span class="material-symbols-outlined">shopping_cart</span>
        </h2>
    </div>

    <!-- CONTENT -->
    <div class='content'>

        <!-- ITEM DATA -->
        <div class='item-data' data-item_id="<?= $item->get_id() ?>">

            <!--ITEM DETAILS-->
            <div class="item-card">
                <!-- MAIN PIC -->
                <div class='img'>
                    <?php if ($item->has_no_picture()): ?>
                        <img src="https://upload.wikimedia.org/wikipedia/commons/f/f3/Question_mark_alternate.png" />
                    <?php else: ?>
                        <img src="<?= $main_pic->get_picture_path() ?>" alt="Item picture">
                    <?php endif; ?>
                </div>

                <!-- INFO -->
                <div class='item-details'>
                    <h1><?= Tools::sanitize($item->get_title()) ?></h1>
                    <p class="desc"><?= Tools::sanitize($item->get_description()) ?? "No description" ?></p>
                    <p class="desc"><strong>Start:</strong> <?= $item->get_created_at() ?></p>
                    <p class="desc"><strong>Ends:</strong> <?= $item_status->get_end_at() ?></p>
                </div>
            </div>

            <?php if ($item->has_many_pictures()): ?>
                <!-- THUMBNAILS -->
                <div id="picture_gallery">
                    <p>Additional Images</p>
                    <div class="images">
                        <?php /** @var ItemPicture $pic */
                        foreach ($pictures as $pic): ?>
                            <a href="item/show/<?= $item->get_id() ?>/<?= $pic->get_priority() ?>">
                                <img class="thumb" src="<?= $pic->get_picture_path() ?>">
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- BIDS HISTORY -->
            <div class="bid-history">
                <div class='row'>
                    <h3>Bid History</h3>
                    <?php if (!empty($bids)): ?>
                        <div class='entries'>
                            <b><?= count($bids); ?> entries</b>
                        </div>
                    <?php endif; ?>
                </div>

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
                                <tr <?= $bid->get_is_highest_bid() ? "class='highlight'" : '' ?>>
                                    <td><?= Tools::sanitize($bid->get_owner()->get_pseudo()) ?></td>
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

        </div>

        <div class='seller-data'>
            <!-- BIDS/BUY -->
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

                <!-- is the item biddable (by the connected member) ? -->
                <?php if ($item_status->is_biddable($member)): ?>
                    <form method="POST" action=<?= $bid_path ?> class="form-box">
                        €<input type="text" id="bid_amount" name="bid_amount" class="input" value="<?= ($currentBid + 1) ?>">
                        <br>
                        <br>
                        <button type="submit">Place Bid</button>
                    </form>
                <?php endif; ?>

                <!-- is the item buyable (by the connected member) ? -->
                <?php if ($item_status->is_buyable($member)): ?>
                    <form method="POST" action=<?= $buy_path ?> class="form-box">
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
                    <img src="<?= $item->get_owner()->get_picture_path() ?>" width="100" alt="<?= Tools::sanitize($item->get_owner()->get_pseudo()) ?>&apos;s photo!">
                <?php endif; ?>
                <label class="label"><?= Tools::sanitize($item->get_owner()->get_pseudo()) ?></label>
                <p class="desc">Member</p>
            </div>

            <?php if ($item->is_owner($member)): ?>
                <!-- OWNER'S ACTIONS -->
                <div class="manage-item">
                    <h3>Manage Your Item</h3>
                    <?php if (!$item_status->get_has_bids()): ?>
                        <a href="item/edit/<?= $item->get_id() ?>" class="row">
                            <div class="row-title">Edit Item Details</div>
                        </a>

                        <a href="item/manage_images/<?= $item->get_id() ?>" class="row">
                            <div class="row-title">Manage Images</div>
                        </a>

                        <?php /** @var ItemStatus $item_status */ ?>
                        <a href="my_items/delete/<?= $item->get_id() ?>" class="row">
                            <div class="row-title">Delete Item</div>
                        </a>
                    <?php endif; ?>

                </div>
            <?php endif; ?>
        </div>

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

    </div>
</body>

</html>