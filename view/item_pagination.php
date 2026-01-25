<ul class="pagination">
    <li>
        <a <?= min($page, $pagination) <= 1 ? "class='disabled'" : "href='item/browse/" . ($page - 1) . "'" ?>>
            &laquo;
        </a>
    </li>

    <?php for ($page_nb = 1; $page_nb <= $pagination; $page_nb++): ?>
        <li>
            <a <?= $page_nb == min($page, $pagination) ? "class='active'" : "" ?> href="item/browse/<?= $page_nb ?>">
                <?= $page_nb ?>
            </a>
        </li>
    <?php endfor; ?>

    <li>
        <a <?= $page >= $pagination ? "class='disabled'" : "href='item/browse/" . ($page + 1) . "'" ?>>
            &raquo;
        </a>
    </li>
</ul>