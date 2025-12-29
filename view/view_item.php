<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($item->get_title()) ?></title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="css/item.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

<div class="item-detail">
    <h2 class="page-title">Item open</h2>
    <p>
        <?php
        $pictures = $item->get_pictures();
        if (!empty($pictures)):
            $firstPic = $pictures[0]; // get the first picture
            ?>
            <img src="<?= htmlspecialchars($firstPic->get_picture_path()) ?>" alt="Item picture">
        <?php endif; ?>
    </p>

    <h1><?= htmlspecialchars($item->get_title()) ?></h1>
    <p class="desc"><?= htmlspecialchars($item->get_description() ?? "No description") ?></p>
    <p class="desc"><strong>Start:</strong> <?= htmlspecialchars($item->get_created_at()) ?></p>
    <p class="desc"><strong>Ends:</strong> <?= $item_status->get_end_at() ?></p>
</div>

<div class="price-container">
    <h3 class="desc">Pricing</h3>
    <?php
    $buyNowPrice = $item->get_buy_now_price() ?? 0;
    $currentBid = $currentBid ?? 0;
    ?>
    <label class="label"><strong>Current Bid:</strong> <?= $currentBid > 0 ? "€ " . number_format($currentBid, 2, ",", ".") : "N/A" ?></label>
    <br>
    <label class="label"><strong>Buy Now:</strong> <?= $buyNowPrice > 0 ? "€ " . number_format($buyNowPrice, 2, ",", ".") : "N/A" ?></label>
</div>

<div class="seller-container">
    <h3 class="desc">Seller Information</h3>
    <?php if (!$item->get_owner()->get_picture_path()): ?>
        <p>You have no picture.</p>
        <p>Set it up <a href="member/profile_picture">here</a></p>
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

</body>
</html>