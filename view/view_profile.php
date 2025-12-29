<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <base href="<?= $web_root ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/profile.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="container">
        <h2 class="page-title">Profile</h2>

        <div class="section">
            <?php if (!$member->get_picture_path()): ?>
                <p>You have no picture.</p>
                <p>Set it up <a href="member/profile_picture">here</a></p>
            <?php else: ?>
                <img src="<?= $member->get_picture_path() ?>" width="100" alt="<?= $member->get_pseudo() ?>&apos;s photo!">
            <?php endif; ?>

            <br>
            <p><?= $member->get_full_name() ?></p>
            <p>@<?= $member->get_pseudo() ?></p>
            <p><?= $member->get_email() ?></p>
        </div>

        <div class="section">
            <h3>My Activities</h3>

            <a href="?" class="row">
                <div class="row-title">Sales</div>
                <div class="row-desc">Items you have sold</div>
            </a>

            <a href="?" class="row">
                <div class="row-title">Purchases</div>
                <div class="row-desc">Items you have purchased</div>
            </a>
        </div>

        <div class="section">
            <h3>Account Settings</h3>

            <a href="profile/edit_profile" class="row">
                <div class="row-title">Edit profile</div>
                <div class="row-desc">Update your personal information</div>
            </a>

            <a href="profile/change_password" class="row">
                <div class="row-title">Change Password</div>
                <div class="row-desc">Change your account security</div>
            </a>

            <a href="profile/profile_picture" class="row">
                <div class="row-title">Profile Picture</div>
                <div class="row-desc">Upload or change your profile picture</div>
            </a>

            <form action="test/logout" method="post" class="logout-form" id="logout-form"></form>
            <button type="submit" name="logout" class="row logout" form="logout-form">
                <div class="logout-title">Logout</div>
                <div class="logout-desc">Sign out of your account</div>
            </button>

        </div>
</body>

</html>