<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <base href="<?= $web_root ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/styles.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="title">Sign Up</div>
<div class="menu">
    <a href="main/index">Home</a>
</div>
<div class="main">
    Please enter your details to sign up :
    <br><br>
    <form id="signupForm" action="test/signup" method="post">
        <table>
            <tr>
                <td>Email:</td>
                <td><input id="email" name="email" type="email" value="<?= $email ?>"></td>
            </tr>
            <tr>
                <td>Full Name:</td>
                <td><input id="full_name" name="full_name" type="text" value="<?= $full_name ?>"></td>
            </tr>
            <tr>
                <td>Pseudo:</td>
                <td><input id="pseudo" name="pseudo" type="text" size="16" value="<?= $pseudo ?>"></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input id="password" name="password" type="password" size="16" value="<?= $password ?>"></td>
            </tr>
            <tr>
                <td>Confirm Password:</td>
                <td><input id="password_confirm" name="password_confirm" type="password" size="16" value="<?= $password_confirm ?>"></td>
            </tr>
        </table>
        <input type="submit" value="Sign Up">
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
