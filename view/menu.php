<?php

/**
 * @var User $user
 */
?>

<ul class="menu">
    <!-- USER -->
    <?php if (isset($user) && $user->get_id() !== null): ?>
        <li>
            <a href="browse">
                <span class="material-symbols-outlined">travel_explore</span>
                <br>Browse
            </a>
        </li>

        <li>
            <a href="myitems">
                <span class="material-symbols-outlined">home</span>
                <br>My Items
            </a>
        </li>

        <li>
            <a href="add">
                <span class="material-symbols-outlined">add</span>
                <br>Add Offer
            </a>
        </li>

        <li>
            <a href="profile">
                <span class="material-symbols-outlined">settings</span>
                <br>Profile
            </a>
        </li>

        <!-- GUEST -->
    <?php else: ?>
        <li>
            <a href="browse">
                <span class="material-symbols-outlined">travel_explore</span>
                <br>Browse
            </a>
        </li>
        <li>
            <a href="signup">
                <span class="material-symbols-outlined">celebration</span>
                <br>Join us
            </a>
        </li>
    <?php endif; ?>
</ul>