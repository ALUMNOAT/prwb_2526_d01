<?php
/**
 * @var User $user
 */
?>

<ul class="menu">
    <?php if (isset($user) && !$user->is_guest()): ?>
        <li><a href="browse"><span class="material-symbols-outlined">travel_explore</span> Browse</a></li>
        <li><a href="myitems"><span class="material-symbols-outlined">home</span> My Items</a></li>
        <li><a href="add"><span class="material-symbols-outlined">add</span> Add Offer</a></li>
        <li><a href="profile"><span class="material-symbols-outlined">settings</span> Profile</a></li>
    <?php else: ?>
        <li><a href="browse"><span class="material-symbols-outlined">travel_explore</span> Browse</a></li>
        <li><a href="signup"><span class="material-symbols-outlined">celebration</span> Join us</a></li>
    <?php endif; ?>
</ul>