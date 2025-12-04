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
    <div class="browse">
        <?php foreach ($available_items as $item_status):
            $item = $item_status->get_item(); ?>
            <div class="card">
                <img src="<?= $item->get_main_picture_path() ?>" alt="Avatar" style="width:100%">
                <div class="container">
                    <h4><b><?= $item->get_title() ?></b></h4>
                    <p><?= $item_status->ends_in() ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <ul class="menu">
        <li><a href="browse">Browse</a></li>
        <li><a href="myitems">My Items</a></li>
        <li><a href="add">Add Offer</a></li>
        <li><a href="profile">Profile</a></li>
    </ul>

</body>

</html>