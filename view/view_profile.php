<?php
/* 
doc
*/

/** 
 * @var Member $user
 * @var array $errors
 * @var string $success
 */

$icons = "account_circle,add,arrow_back,celebration,home,logout,manage_accounts,password,receipt_long,save,settings,shopping_cart,travel_explore";
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <base href="<?= $web_root ?>" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=<?= $icons ?>">
    <link rel="stylesheet" href="css/icons.css">
    <link href="css/profile.css" rel="stylesheet" type="text/css" />
    <link href="css/menu.css" rel="stylesheet" type="text/css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>

<body>
    <div class="container">
        <h2 class="page-title">Profile</h2>

        <div class="section">
            <?php if (!$user->get_picture_path()): ?>
                <p>You have no picture.</p>
                <p>Set it up <a href="profile/profile_picture">here</a></p>
            <?php else: ?>
                <img src="<?= $user->get_picture_path() ?>" width="100" alt="<?= $user->get_pseudo() ?>&apos;s photo!">
            <?php endif; ?>

            <br>
            <p><?= Tools::sanitize($user->get_full_name() ?? "") ?></p>
            <p>@<?= Tools::sanitize($user->get_pseudo() ?? "") ?></p>
            <p><?= Tools::sanitize($user->get_email() ?? "") ?></p>
        </div>

        <div class="section">
            <h3>My Activities</h3>
            
            <!-- SALES -->
            <a href="my_items/sales" class="row">
                <span class="material-symbols-outlined size-40">receipt_long</span>
                <div>
                    <div class="row-title">Sales</div>
                    <div class="row-desc">Items you have sold</div>
                </div>
            </a>

            <!-- PURCHASES -->
            <a href="my_items/purchases" class="row">
                <span class="material-symbols-outlined size-40">shopping_cart</span>
                <div>
                    <div class="row-title">Purchases</div>
                    <div class="row-desc">Items you have purchased</div>
                </div>
            </a>
        </div>

        <div class="section">
            <h3>Account Settings</h3>

            <!-- EDIT PROFILE -->
            <a href="profile/edit_profile" class="row">
                <span class="material-symbols-outlined size-40">manage_accounts</span>
                <div>
                    <div class="row-title">Edit profile</div>
                    <div class="row-desc">Update your personal information</div>
                </div>
            </a>

            <!-- CHANGE PASSWORD -->
            <a href="profile/change_password" class="row">
                <span class="material-symbols-outlined size-40">password</span>
                <div>
                    <div class="row-title">Change Password</div>
                    <div class="row-desc">Change your account security</div>
                </div>
            </a>

            <!-- PROFILE PICTURE -->
            <a href="profile/profile_picture" class="row">
                <span class="material-symbols-outlined size-40">account_circle</span>
                <div>
                    <div class="row-title">Profile Picture</div>
                    <div class="row-desc">Upload or change your profile picture</div>
                </div>
            </a>

            <!-- LOGOUT -->
            <form action="test/logout" method="post" class="logout-form" id="logout-form"></form>
            <button type="submit" name="logout" class="row logout" form="logout-form">
                <span class="material-symbols-outlined size-40">logout</span>
                <div>
                    <div class="logout-title">Logout</div>
                    <div class="logout-desc">Sign out of your account</div>
                </div>
            </button>

        </div>
    </div>

    <!-- NAVBAR MENU -->
    <?php require 'menu.php' ?>
</body>

</html>