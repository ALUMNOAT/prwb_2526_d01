<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Browse</title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="css/browse.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div>
        <h1>My Bids</h1>
    </div>

    <div class="browse">
        <!-- 
        doc: https://www.w3schools.com/howto/howto_css_image_text.asp 
        doc: https://www.w3schools.com/icons/tryit.asp?filename=tryicons_google-collections

        -->
        <?php foreach ($my_bidded_items as $item_status):
            $item = $item_status->get_item(); ?>
            <div class="card">
                <div class="container_image">
                    <img src="<?= $item->get_pictures()[1]->get_picture_path() ?>" alt="Avatar" style="width:100%">

                    <div class="bottom-left">Bottom Left</div>

                    <div class="top-left">Auction</div>

                    <div class="top-right">Auction</div>

                    <?php if (count($item->get_pictures()) > 1): ?>
                        <div class="bottom-right">
                            <i class="material-icons md-18">collections</i><?= count($item->get_pictures()) ?> images
                        </div>
                    <?php endif; ?>

                    <div class="centered">Centered</div>
                </div>
                <div class="container">
                    <h4><b><?= $item->get_title() ?></b></h4>
                    <p>by <?= $item->get_owner()->get_full_name() ?></p>
                    <p><?= $item_status->ends_in() ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div>
        <h1>Available Items</h1>
    </div>

    <div class="browse">
        <?php foreach ($available_items as $item_status):
            $item = $item_status->get_item(); ?>
            <div class="card">
                <div class="container_image">
                    <img src="<?= $item->get_pictures()[1]->get_picture_path() ?>" alt="Avatar" style="width:100%">

                    <div class="bottom-left">Bottom Left</div>

                    <div class="top-left">Auction</div>

                    <div class="top-right">Auction</div>

                    <?php if (count($item->get_pictures()) > 1): ?>
                        <div class="bottom-right"><?= count($item->get_pictures()) ?> images</div>
                    <?php endif; ?>

                    <div class="centered">Centered</div>
                </div>
                <div class="container">
                    <h4><b><?= $item->get_title() ?></b></h4>
                    <p>by <?= $item->get_owner()->get_full_name() ?></p>
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