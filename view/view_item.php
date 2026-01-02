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
<div class="margin">
    <p>
        <?php if (isset($member)): ?>
            <a href="login/logout">Logout</a>
        <?php else: ?>
            <a href="login">Login</a>
        <?php endif; ?>
    </p>
</div>

<?php require 'menu.php'; ?>

<div class="item-detail">
    <h1><?= htmlspecialchars($item->get_title()) ?></h1>
    <p><strong>Owner:</strong> <?= htmlspecialchars($item->get_owner()->get_pseudo()) ?></p>
    <p><strong>Description:</strong> <?= htmlspecialchars($item->get_description() ?? "No description") ?></p>
    <p><strong>Created At:</strong> <?= htmlspecialchars($item->get_created_at()) ?></p>
    <p><strong>Duration:</strong> <?= $item->get_duration_days() ?> days</p>

    <?php if (!empty($item->get_pictures())): ?>
        <div class="item-pictures">
            <?php foreach ($item->get_pictures() as $pic): ?>
                <img src="<?= htmlspecialchars($pic->get_picture_path()) ?>" alt="Item picture">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <p><strong>Buy Now Price:</strong> <?= $item->get_buy_now_price() ?? "N/A" ?></p>
    <p><strong>Starting Bid:</strong> <?= $item->get_starting_bid() ?? "N/A" ?></p>
</div>

</body>
</html>
