<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <base href="<?= $web_root ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/signup.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="signup-container">
        <div class="signup-box">
            <h1>Sign up</h1>

            <hr>

            <!-- LOGIN FORM -->
            <form action="test/signup" method="post">
                <input type="email" id="email" name="email" placeholder="Email" value="<?= $email ?? null ?>" required>
                <input type="text" id="full_name" name="full_name" placeholder="Full Name" value="<?= $full_name ?? null ?>" required>
                <input type="text" id="pseudo" name="pseudo" size="16" placeholder="Pseudo" value="<?= $pseudo ?? null ?>" required>
                <input type="password" id="password" name="password" size="16" placeholder="Password" value="<?= $password ?? null ?>" required>
                <input type="password" id="password_confirm" name="password_confirm" size="16" placeholder="Confirm your password" value="<?= $password_confirm ?? null ?>" required>
                <button type="submit" class="btn-signup">Sign Up</button>
            </form>

            <!-- CANCEL -->
            <!-- https://www.geeksforgeeks.org/html/how-to-create-an-html-button-that-acts-like-a-link/ -->
            <form action='test/login'>
                <button type="submit" class="btn-cancel">Cancel</button>
            </form>

            <!-- ERRORS -->
            <?php if (isset($errors) && count($errors) != 0): ?>
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
</body>

</html>