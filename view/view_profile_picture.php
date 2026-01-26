<?php
/* 
doc
*/

/** 
 * @var Member $user
 * @var array $errors
 * @var string $success
 */
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Profile Picture</title>
    <base href="<?= $web_root ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=add,arrow_back,home,save,settings,shopping_cart,travel_explore">
    <link rel="stylesheet" href="css/icons.css">
    <link rel="stylesheet" href="css/top_navbar.css">
    <link href="css/profile_picture.css" rel="stylesheet" type="text/css" />
    <link href="css/menu.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="top-navbar">
        <a href="profile">
            <span class="material-symbols-outlined size-40">arrow_back</span>
        </a>

        <h2 class="page-title">
            Manage Profile Picture
            <span class="material-symbols-outlined">shopping_cart</span>
        </h2>
    </div>

    <div class="container">
        <div class="section">
            <h3>Current Profile Picture</h3>

            <div class="profile-content">
                <?php if (!$user->get_picture_path()): ?>
                    <p>You have no picture.</p>
                <?php else: ?>
                    <img src="<?= $user->get_picture_path() ?>" alt="<?= $user->get_pseudo() ?>&apos;s photo!">
                <?php endif; ?>
                <br>
                <p><strong><?= $user->get_full_name() ?></strong></p>
                <p>@<?= $user->get_pseudo() ?></p>
            </div>
        </div>

        <form method="POST" action='profile/profile_picture' class="form-box" enctype="multipart/form-data">
            <div class="section">
                <h3>Upload New Picture</h3>

                <label class="label"><strong>Select Image *</strong></label>
                Choose File : <input type="file" id="file" name="image" class="input" placeholder="No file chosen" accept="image/jpg, image/jpeg, image/x-png, image/gif, image/webp">
                <p class="desc">Supported formats: JPG, PNG, GIF, WebP. This will replace your current profile picture.</p>

                <button type="submit" name="upload" class="btn">Upload Picture</button>
            </div>

            <div class="section">
                <h3>Remove Current Picture</h3>

                <p>This will permanently delete your current profile picture. You can upload a new one anytime.</p>
                <button type="submit" name="delete" class="btn-delete">Delete Picture</button>
            </div>

        </form>

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
            <p><span class='success'><?= $success ?></span></p>
        <?php endif; ?>

    </div>

    <!-- NAVBAR MENU -->
    <?php require 'menu.php' ?>
</body>

</html>