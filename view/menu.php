<?php

/**
 * @var User $user
 */
?>

<ul class="menu">
    <!-- ADMIN -->
    <?php if ($user->is_admin()): ?>
        <li>
            <a href="item/browse/1">
                <span class="material-symbols-outlined size-40">travel_explore</span>
                <br>Browse
            </a>
        </li>

        <li>
            <a href="myitems">
                <span class="material-symbols-outlined size-40">home</span>
                <br>My Items
            </a>
        </li>

        <li>
            <a href="item/add">
                <span class="material-symbols-outlined size-40">add</span>
                <br>Add Offer
            </a>
        </li>

        <li>
            <a href="profile">
                <span class="material-symbols-outlined size-40">settings</span>
                <br>Profile
            </a>
        </li>

        <li>
            <a href="category">
                <span class="material-symbols-outlined size-40">category_search</span>
                <br>Categories
            </a>
        </li>

        <li>
            <a href="exam">
                <span class="material-symbols-outlined size-40">exam</span>
                <br>Exam
            </a>
        </li>

        
    <!-- USER -->
    <?php elseif (!$user->is_guest()): ?>
        <li>
            <a href="item/browse/1">
                <span class="material-symbols-outlined size-40">travel_explore</span>
                <br>Browse
            </a>
        </li>

        <li>
            <a href="myitems">
                <span class="material-symbols-outlined size-40">home</span>
                <br>My Items
            </a>
        </li>

        <li>
            <a href="item/add">
                <span class="material-symbols-outlined size-40">add</span>
                <br>Add Offer
            </a>
        </li>

        <li>
            <a href="profile">
                <span class="material-symbols-outlined size-40">settings</span>
                <br>Profile
            </a>
        </li>

        <!-- GUEST -->
    <?php else: ?>
        <li>
            <a href="item/browse/1">
                <span class="material-symbols-outlined size-40">travel_explore</span>
                <br>Browse
            </a>
        </li>
        <li>
            <a href="signup">
                <span class="material-symbols-outlined size-40">celebration</span>
                <br>Join us
            </a>
        </li>
    <?php endif; ?>
</ul>