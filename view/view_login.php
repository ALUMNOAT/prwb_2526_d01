<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Log In</title>
    <base href="<?= $web_root ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/login.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<h1 class="app-title">EPayFC</h1>
<img class="app-logo" src="https://static.wikia.nocookie.net/gensin-impact/images/2/24/Raiden_Shogun_Icon.png" alt="icon">
<div class="login-container">
    <div class="login-box">
        <h1>Sign in</h1>
        <hr>
        <form action="test/login" method="post">
            <input type="text" id="email" name="email" placeholder="Mail" value="<?= $email ?>" required>
            <input type="password" id="password" name="password" placeholder="******" required>
            <button type="submit" class="btn-login">Login</button>
            <button type="button" class="btn-guest" onclick="window.location.href='test/index'">Continue as guest</button>
        </form>
        <p class="signup-link">
            <a href="test/signup">New here? Click here to subscribe!</a>
        </p>
        <hr>

        <?php if (count($errors) != 0): ?>
            <div class="errors">
                <p>Please correct the following error(s) :</p>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="debug">
            <h3>For Debug Purpose</h3>
            <p>Login as <a href="index.php?controller=test&action=login_as&email=boverhaegen@epfc.eu" class="white"><strong>boverhaegen@epfc.eu</strong></a></p>
            <p>Login as <a href="index.php?controller=test&action=login_as&email=mamichel@epfc.eu" class="white"><strong>mamichel@epfc.eu</strong></a></p>
            <p>Login as <a href="index.php?controller=test&action=login_as&email=quhouben@epfc.eu" class="white"><strong>quhouben@epfc.eu</strong></a></p>
            <p>Login as <a href="index.php?controller=test&action=login_as&email=xapigeolet@epfc.eu" class="white"><strong>xapigeolet@epfc.eu</strong></a></p>
            <a href="#" class="green">Restore original data</a>
            <a href="#" class="orange">Backup personal data</a>
            <a href="#" class="orange">Restore personal data</a>
        </div>
    </div>
</div>
</body>
</html>
