<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Test page</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <h2>Test Page</h2>
    <h1>Hello PRWB_2526_D01 !</h1>
    <br>
    <p><?= $member->get_id() ?></p>
    <p><?= $member->get_full_name() ?></p>
    <p><?= $member->get_email() ?></p>
    <p><?= $member->get_pseudo() ?></p>
    <p><?= $member->get_password() ?></p>
    <p><?= $member->get_role()->value ?></p>
    <p><?= $member->get_picture_path() ?? "null" ?></p>
    <p><?= $member->get_iban() ?? "null" ?></p>
    <br>
    <p><a href="">Back to index</a></p>
    <p><a href='time'>Time management</a>
    <p>
</body>

</html>