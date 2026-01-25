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
    <title>Change Password</title>
    <base href="<?= $web_root ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=add,arrow_back,home,save,settings,travel_explore">
    <link rel="stylesheet" href="css/icons.css">    
    <link href="css/edit_profile.css" rel="stylesheet" type="text/css" />
    <link href="css/menu.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="profile-navbar">
        <a href="profile">
            <button>
                <span class="material-symbols-outlined size-40">arrow_back</span>
            </button>
        </a>

        <h2 class="page-title">
            Edit profile
        </h2>

        <button type="submit" form="profile">
            <span class="material-symbols-outlined size-40">save</span>
        </button>
    </div>

    <div class="container">

        <div class="form-box">
            <form name="profile" id="profile" method="POST" action='profile/edit_profile'>
                <div class="section">
                    <h3>Personal Information</h3>

                    <label for="full_name" class="label">Full Name *</label>
                    <input form="profile" type="text" id="full_name" name="full_name" class="input" value="<?= $user->get_full_name() ?>" required>
                    <p class="desc">Your complete name as it should appear on your profile</p>

                    <label for="pseudo" class="label">Username *</label>
                    <input form="profile" type="text" id="pseudo" name="pseudo" class="input" value="<?= $user->get_pseudo() ?>" required>
                    <p class="desc">This will be your public display name on the platform</p>
                </div>

                <div class="section">
                    <h3>Contact & Payment</h3>

                    <label for="email" class="label">Email Address *</label>
                    <input form="profile" type="email" id="email" name="email" class="input" value="<?= $user->get_email() ?>" required>
                    <p class="desc">We'll use this email to send you notifications about your sales and purchases</p>

                    <label for="iban" class="label">IBAN <span class="secondary">(Optional)</span></label>
                    <!--Magie. Ici on separe l'IBAN par 4 pour la lisibilité -->
                    <input form="profile" type="text" id="iban" name="iban" class="input" <?= $user->get_iban() !== null ? 'value="' . trim(chunk_split($user->get_iban(), 4, " ")) . '"' : 'placeholder="BE99 9999 9999 9999 9999"' ?>>
                    <p class="desc">For receiving payments from your sales. Leave this empty if you don't want to receive payments directly.</p>
                </div>
            </form>
        </div>

        <?php if (count($errors) != 0): ?>
            <div class='errors'>
                <p>Please correct the following error(s) :</p>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

    </div>

    <!-- NAVBAR MENU -->
    <?php require 'menu.php' ?>
</body>

</html>