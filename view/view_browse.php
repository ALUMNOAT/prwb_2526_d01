<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Browse</title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="css/browse.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <ul>
        <?php foreach($available_items as $item_status): ?>
            <li><a href="item/<?= $item_status->get_item()->get_id() ?>">
                <?= $item_status->get_end_at()." -> ".$item_status->get_item()->get_title(); ?>
            </a></li>
        <?php endforeach; ?>
    </ul>

    <ul class="menu">
        <li><a href="browse">
            Browse
        </a></li>
        <li><a href="myitems">
            My Items
        </a></li>
        <li><a href="add">
            Add Offer
        </a></li>
        <li><a href="profile">
            Profile
        </a></li>
    </ul>
    
</body>
</html>