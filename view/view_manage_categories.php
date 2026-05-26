<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="css/manage_categories.css">
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
        $cat_sum = $category->howMany();
        ?>

        <!-- On ajoute data-id pour que JS sache quoi déplacer et dans quel ordre-->
        <div class="section" data-id="<?= $cat_id ?>">
            <form method="POST" action="category" class="form-box">
                <!-- ID caché pour pouvoir faire les requetes SQL facilement -->
                <input type="hidden" name="category_id" value="<?= $cat_id ?>">

                <span class="drag-handle">≡</span>

                <input type="text" name="name" class="input" value="<?= Tools::sanitize($cat_name)?>" required>

                <span class="only-js">(<?= $cat_sum?>)</span>

                <button type="submit" name="action" value="up" class="btn-order only-no-js"
                <?php if ($cat_priority === 1) {
                    echo "disabled";
                } ?>
                >Up</button>
                <button type="submit" name="action" value="down" class="btn-order only-no-js"
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
                <span id="error-name-category" class="error-message" style="color: red; display: none;"></span>
            </form>
        </div>

    <?php endforeach; ?>
    </div>

    <hr>
    <div class="section-ajout">
        <form method="POST" action="category" class="form-box">
            <input type="text" id="new_category" name="new_category" class="new_category" value=" ">
            <button type="submit" name="action" value="add">+</button>
            <span id="error-name-category-ajout" class="error-message" style="color: red; display: none;"></span>
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

    <!-- On ajoute la classe js-enabled au body, comme ca pas besoin de s'amuser a changer chaque bouton -->
    <script>document.body.classList.add('js-enabled');</script>

    <!-- On modifie le texte du bouton "Save" en "Edit" -->
    <script>
    document.querySelectorAll('button[value="save"]').forEach(btn => {
        btn.innerText = "Edit";
    });
    </script>

    <!-- On fait que cliquer sur enter ne trigger pas le 1er submit = UP-->
    <script>
        document.querySelectorAll('.input').forEach(input => {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault(); // Empêche le formulaire de se soumettre
                }
            });
        });
    </script>

    <!-- On fait que le cursor est sur la derniere lettre-->
    <script>
        document.querySelectorAll('.input').forEach(input => {
            input.addEventListener('focus', (e) => {
                // Sauvegarde la valeur actuelle
                const val = e.target.value;
                // Déplace le curseur à la longueur totale de la valeur
                e.target.selectionStart = val.length;
                e.target.selectionEnd = val.length;
            });
        });
    </script>

    <!--Script de drag&drop natif (IA) (voir CSS aussi) (on ne touche pas, sauf classe section qui peut etre différente)-->
    <script>
    const container = document.querySelector('.container');

    // On rend les sections déplaçables
    container.querySelectorAll('.section').forEach(section => {
        section.setAttribute('draggable', true);
        
        section.addEventListener('dragstart', (e) => {
            e.target.classList.add('dragging');
        });

        section.addEventListener('dragend', (e) => {
            e.target.classList.remove('dragging');
            // C'est ici qu'on appellera le 'fetch' pour envoyer le nouvel ordre au serveur
            saveNewOrder(); 
        });
    });

    container.addEventListener('dragover', (e) => {
        e.preventDefault(); // Indispensable pour autoriser le drop
        const draggingItem = document.querySelector('.dragging');
        const afterElement = getDragAfterElement(container, e.clientY);
        if (afterElement == null) {
            container.appendChild(draggingItem);
        } else {
            container.insertBefore(draggingItem, afterElement);
        }
    });

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.section:not(.dragging)')];
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) return { offset: offset, element: child };
            return closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    // Fonction pour envoyer au serveur (ceci change selon le cas)
    async function saveNewOrder() {
        const sections = document.querySelectorAll('.section');
        // On prépare le tableau des données
        const order = Array.from(sections).map((sec, index) => ({
            // Résultat : On obtient un tableau d'objets simple comme : [{id: 1, priority: 1}, {id: 5, priority: 2}, ...] qui représente le nouvel ordre souhaité.
            id: sec.dataset.id,
            priority: index + 1
        }));

        try {
            const response = await fetch('category/update_order_service', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ order: order })
            });

            const result = await response.json();
            // console.log(result.received); // debug
            if (result.status === 'success') {
                console.log("Ordre mis à jour en base de données !");
            }
        } catch (error) {
            console.error("Erreur lors de la mise à jour :", error);
        }
    }
    </script>
    
    <!-- Script de vérif de regles métiers (nom) -->
    <script src="scripts/validations_manage_categories.js" type="text/javascript"></script>

    </body>
</html>