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
    <?php if(isset($member) && $member !== null): ?>
        <p>id: <?= $member->get_id() ?? "null" ?></p>
        <p>full_name: <?= $member->get_full_name() ?></p>
        <p>email: <?= $member->get_email() ?></p>
        <p>pseudo: <?= $member->get_pseudo() ?></p>
        <p>password_hash: <?= $member->get_password() ?></p>
        <p>role: <?= $member->get_role()->value ?></p>
        <p>picture_path: <?= $member->get_picture_path() ?? "null" ?></p>
        <p>IBAN: <?= $member->get_iban() ?? "null" ?></p>
    <?php else: ?>
        <p>Member not found</p>
    <?php endif; ?>
    <br>
    <p><a href="">Back to index</a></p>
    <p><a href='time'>Time management</a>
    <p>
</body>

</html>