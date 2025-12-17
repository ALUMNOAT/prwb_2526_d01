<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Profile Picture</title>
    <base href="<?= $web_root ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/profile_picture.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="container">
    <h2 class="page-title">Manage profile picture</h2>

        <div class="section">
            <h3>Current Profile Picture</h3>

            <div class="profile-content">
            <?php if (!$member->get_picture_path()): ?>
                <p>You have no picture.</p>
            <?php else: ?>
                <img src="<?= $member->get_picture_path()?>" width="100" alt="<?= $member->get_pseudo() ?>&apos;s photo!">
            <?php endif; ?>
            <br>
            <p><strong><?=$member->get_full_name() ?></strong></p>
            <p>@<?= $member->get_pseudo()?></p>
            </div>
        </div>

    <form method="POST" action='member/profile_picture' class="form-box">

        <div class="section">
            <h3>Upload New Picture</h3>

            <label class="label"><strong>Select Image *</strong></label>
            Choose File : <input type="file" id="file" name="file" class="input" placeholder="No file chosen" accept="image/jpg, image/jpeg, image/x-png, image/gif, image/webp">
            <p class="desc">Supported formats: JPG, PNG, GIF, WebP. This will replace your current profile picture.</p>

            <button type="submit" class="btn">Upload Picture</button>
        </div>

        <div class="section">
            <h3>Remove Current Picture</h3>

            <p>This will permanently delete your current profile picture. You can upload a new one anytime.</p>
            <button type="submit" class="btn-delete">Delete Picture</button>
        </div>

    </form>

    <?php if(count($errors)!=0): ?>
        <div class='errors'>
            <p>Please correct the following error(s) :</p>
            <ul>
                <?php foreach($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif (strlen($success) != 0): ?>
        <p><span class='success'><?= $success ?></span></p>
    <?php endif; ?>

</div>
</body>
</html>