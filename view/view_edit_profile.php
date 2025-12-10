<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <base href="<?= $web_root ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/edit_profile.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="container">
    <h2 class="page-title">Edit profile</h2>

    <form method="POST" action='member/edit_profile' class="form-box">

        <div class="section">
            <h3>Personal Information</h3>

            <label class="label">Full Name *</label>
            <input type="text" id="full_name" name="full_name" class="input" value="<?= $member->get_full_name() ?>" required>
            <p class="desc">Your complete name as it should appear on your profile</p>

            <label class="label">Username *</label>
            <input type="text" id="pseudo" name="pseudo" class="input" value="<?= $member->get_pseudo() ?>" required>
            <p class="desc">This will be your public display name on the platform</p>
        </div>

        <div class="section">
            <h3>Contact & Payment</h3>

            <label class="label">Email Address *</label>
            <input type="email" id="email" name="email" class="input" value="<?= $member->get_email() ?>" required>
            <p class="desc">We'll use this email to send you notifications about your sales and purchases</p>

            <label class="label">IBAN <span class="secondary">(Optional)</span></label>
            <input type="text" id="iban" name="iban" class="input" <?= $member->get_iban() !== null ? 'value="'. $member->get_iban() . '"' : 'placeholder="BE99 9999 9999 9999 9999"' ?>>
            <p class="desc">For receiving payments from your sales. Leave this empty if you don't want to receive payments directly.</p>
        </div>

        <button type="submit" class="btn">Save</button>
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
    <?php endif; ?>

</div>
</body>
</html>