<?php
/* 
doc:
*/

/** 
 * @var Member $user
 * @var Item $item
 */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Browse</title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=add,celebration,filter,gavel,home,schedule,settings,shopping_bag,travel_explore,trophy" />
    <link rel="stylesheet" href="css/icons.css">
    <link rel="stylesheet" href="css/time.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class='content'>
       <div class='card'>
            <h1 class='warning'>Are you sure ?</h1>
            <br>
            <p>Do you really want to delete item <b>"<?= $item->get_title() ?>"</b> by <?= $user->get_full_name() ?> and all of its dependencies ?</p>
            <p>This process cannot be undone.</p>
            <form action='my_items/delete/<?= $item->get_id() ?>' method='POST' id='delete' name='delete'>
                <a href='my_items'>Cancel</a>
                <button id='confirm' name='confirm' form='delete' type='submit' value='<?= $item->get_id() ?>'>Delete</button>
            </form>
       </div>
    </div>

    <?php if (Configuration::is_dev()): ?>
        <!-- TIME MANAGEMENT DEV -->
        <?php require 'time.php' ?>
    <?php endif; ?>
</body>
