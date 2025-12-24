<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Log In</title>
    <base href="<?= $web_root ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/login.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <h1 class="app-title">EPayFC</h1>

    <img class="app-logo" src="https://static.wikia.nocookie.net/gensin-impact/images/2/24/Raiden_Shogun_Icon.png" alt="icon">

    <div class="login-container">
        <div class="login-box">
            <h1>Sign in</h1>

            <hr>

            <!-- LOGIN FORM -->
            <form action="login" method="post">
                <input type="text" id="email" name="email" placeholder="Mail" value="<?= $email ?? null ?>" required>
                <input type="password" id="password" name="password" placeholder="***" required>
                <button type="submit" class="btn-login">Login</button>
            </form>

            <!-- GUEST -->
            <!-- https://www.geeksforgeeks.org/html/how-to-create-an-html-button-that-acts-like-a-link/ -->
            <form action='login/guest'>
                <button type="submit" class="btn-guest">Continue as guest</button>
            </form>

            <!-- SIGNUP LINK -->
            <p class="signup-link">
                <a href="signup">New here? Click here to subscribe!</a>
            </p>

            <hr>

            <!-- ERRORS -->
            <?php if (isset($errors) && count($errors) != 0): ?>
                <div class="errors">
                    <p>Please correct the following error(s) :</p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- LOGIN DEBUG -->
            <div class="debug">
                <h3>For Debug Purpose</h3>
                <p>Login as <a href="login/login_as/Boris" class="white"><strong>boverhaegen@epfc.eu</strong></a></p>
                <p>Login as <a href="login/login_as/Marc" class="white"><strong>mamichel@epfc.eu</strong></a></p>
                <p>Login as <a href="login/login_as/Quentin" class="white"><strong>quhouben@epfc.eu</strong></a></p>
                <p>Login as <a href="login/login_as/Xavier" class="white"><strong>xapigeolet@epfc.eu</strong></a></p>
                <p>Login as <a href="login/login_as/Admin" class="white"><strong>admin@epfc.eu</strong></a></p>

                <a href="#" class="green">Restore original data</a>
                <a href="#" class="orange">Backup personal data</a>
                <a href="#" class="orange">Restore personal data</a>
            </div>
        </div>
    </div>
</body>

</html>