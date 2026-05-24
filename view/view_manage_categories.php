<!DOCTYPE html>
<html>

<head>
</head>

<body>
    <!-- On calcule le maximum de la priorité de la catégorie pour disable le down -->
    <?php
    $max_priority = 0;
    foreach ($all_categories as $cat) {
        if ($cat->get_priority() > $max_priority) {
            $max_priority = $cat->get_priority();
        }
    }
    ?>

    <div class="container">
    <?php foreach ($all_categories as $category):
        $cat_name = Tools::sanitize($category->get_name());
        $cat_id = $category->get_id();
        $cat_priority = $category->get_priority();
        ?>

        <div class="section">
            <form method="POST" action="category" class="form-box">
                <!-- ID caché pour pouvoir faire les requetes SQL facilement -->
                <input type="hidden" name="category_id" value="<?= $cat_id ?>">

                <input type="text" name="name" class="input" value="<?= Tools::sanitize($cat_name)?>" required>

                <button type="submit" name="action" value="up" 
                <?php if ($cat_priority === 1) {
                    echo "disabled";
                } ?>
                >Up</button>
                <button type="submit" name="action" value="down"
                <?php if ($cat_priority === $max_priority) {
                    echo "disabled";
                } ?>
                >Down</button>
                <button type="submit" name="action" value="save">Save</button>
                <button type="submit" name="action" value="delete"
                <?php if ($category->isUsed()) {
                    echo "disabled";
                } ?>
                >Delete</button>
            </form>
        </div>

    <?php endforeach; ?>
    </div>

    <hr>
    <div class="section">
        <form method="POST" action="" class="form-box">
            <input type="text" id="new_category" name="new_category" class="input" value=" ">
            <button type="submit" name="action" value="add">+</button>
        </form>
    </div>

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
</body>

</html>