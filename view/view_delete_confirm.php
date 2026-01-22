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
    <div>
       <p>test</p>
    </div>

    <?php if (Configuration::is_dev()): ?>
        <!-- TIME MANAGEMENT DEV -->
        <?php require 'time.php' ?>
    <?php endif; ?>
</body>
