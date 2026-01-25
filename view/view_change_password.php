<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <base href="<?= $web_root ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=add,arrow_back,home,save,settings,travel_explore">
    <link rel="stylesheet" href="css/icons.css">
    <link href="css/change_password.css" rel="stylesheet" type="text/css" />
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
        <h2 class="page-title">Change password</h2>

        <form name="profile" id="profile" method="POST" action='profile/change_password' class="form-box">
            <div class="section">
                <h3>Current Password</h3>

                <label class="label">Current Password *</label>
                <input type="password" id="current_password" name="current_password" class="input" placeholder="Enter your current password" required>
                <p class="desc">Enter your current password to verify your identity</p>
            </div>

            <div class="section">
                <h3>New Password</h3>

                <label class="label">New Password *</label>
                <input type="password" id="new_password" name="new_password" class="input" placeholder="Enter your new password" required>
                <p class="desc">Password must be 8-16 characters with uppercase, number, and punctuation</p>

                <label class="label">Confirm New Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" class="input" placeholder="Confirm your new password" required>
                <p class="desc">Re-enter your new password to confirm it matches</p>
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
        <?php endif; ?>
    </div>

    <!-- NAVBAR MENU -->
    <?php require 'menu.php' ?>
</body>

</html>