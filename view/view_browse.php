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
            <li><?= $item_status->get_end_at()." -> ".$item_status->get_item()->get_title(); ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>