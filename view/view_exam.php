<!DOCTYPE html>
<html>
    <head>
        <base href="<?= $web_root ?>">
    </head>
    <body>
        <div>
            <form method="POST" action="exam/selected_category_post">
                <div id="categories">
                    <h6>Category</h6>
                    <div>
                        <select name = "category_selected">
                            <option value="0">Choose a category</option>

                            <?php foreach ($categories as $category):
                                $cat_id = $category->get_id();
                                $cat_name = $category->get_name(); 
                            ?>

                            <option value="<?= $cat_id ?>"
                            <?php if ($selected_category == $cat_id):
                                echo "selected";?>
                            <?php endif; ?>>

                            <?= $cat_name ?></option>

                            <?php endforeach; ?>
                        </select>
                        <button type="submit">OK</button>
                    </div>
                </div>
            </form>

            <?php if (isset($selected_category) && $selected_category != 0): ?>
                <div id="users">
                    <h6>User</h6>
                    <div>
                        <select name = "user_selected" onchange="launch()">
                            <option value="0">Choose a user</option>

                            <?php foreach ($users as $user):
                                $user_id = $user->get_id();
                                $user_name = $user->get_full_name();
                                $user_pseudo = $user->get_pseudo();
                            ?>

                            <option value="<?= $user_id ?>">
                            <?= $user_pseudo . " (" . $user_name . ")"?></option>

                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div id="items">
                </div>
        </div>    
            <?php endif; ?>

        <script>

            function get_user_id() {
                let user_container = document.querySelector("select[name='user_selected']");
                user_id = user_container.value;
                console.log(user_id);
                return user_id;
            }

            function get_category_id() {
                let category_container = document.querySelector("select[name='category_selected']");
                cat_id = category_container.value;
                console.log(cat_id);
                return cat_id;
            }

            /* https://stackoverflow.com/questions/4641962/getting-an-option-text-value-with-javascript */

            function get_user_name() {
                let user_container = document.querySelector("select[name='user_selected']");
                user_name = user_container.options[user_container.selectedIndex].text;
                console.log(user_name);
                return user_name;
            }

            function get_category_name() {
                let category_container = document.querySelector("select[name='category_selected']");
                cat_name = category_container.options[category_container.selectedIndex].text;
                console.log(cat_name);
                return cat_name;
            }

            async function get_user_items(cat_id, user_id) {
                try {
                    const response = await fetch("exam/user_get_items_category_service/"+cat_id+"/"+user_id, {
                    method: "GET",
                    headers: {'Content-Type': 'application/json'},
                    });

                    const result = await response.json();
                    if (result.status === 'success') {
                        add_items(result.items);
                        console.log("Recu réponse");
                    }
                } catch (error) {
                    console.error("Erreur lors du fetch :", error);
                }
            }

            /* https://stackoverflow.com/questions/19650108/how-to-delete-a-check-box-using-javascript 
            source d'inspi + retravail */
            function deletecheckedboxes() {
                let checkedBoxes = document.querySelectorAll('input[name="items[]"]:checked');

                checkedBoxes.forEach((checkbox) => {
                    checkbox.parentElement.remove();
                });

                updateButton();
            }


            function updateButton() {
                let bouton = document.getElementById("remove-btn");
                let estcoché = document.querySelectorAll('input[name="items[]"]:checked').length > 0;

                bouton.disabled = !estcoché;
            }

            function add_items(items) {
                item_container = document.getElementById("items");
                item_container.innerHTML = "";

                let user_name = get_user_name();
                let cat_name = get_category_name();

                let html = `<h6>Items from ${user_name} with category: ${cat_name}</h6>`;

                Object.entries(items).forEach(([id, title]) => {
                    html += `
                        <div>
                            <input type="checkbox" name="items[]" onclick="updateButton()" value="${id}">
                            ${title}
                        </div>`;
                });


                html += `<button type="button" id="remove-btn" onclick="delete_category_of_an_item()" disabled>Remove category</button>`;

                item_container.insertAdjacentHTML('afterbegin', html);
            }

            async function delete_category_of_an_item() {
                let cat_id = get_category_id();
                let items_list_html = document.querySelectorAll('input[name="items[]"]');
                let items_list = [];

                items_list_html.forEach((elem) => {
                   let id = elem.value;
                   items_list.push(id);
                });

                console.log(cat_id);
                console.log(items_list);

                try {
                    const response = await fetch("exam/delete_category_of_an_item_service/",
                    {
                        method: "POST",
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ 
                            cat_id : cat_id,
                            items_id : items_list})
                    }
                );

                    const result = await response.json();
                    if (result.status === 'success') {
                        deletecheckedboxes();
                        
                        console.log("A été supprimé");
                    }
                } catch (error) {
                    console.error("Erreur lors du post :", error);
                }
            }

            function launch() {
                let user_id = get_user_id();
                let cat_id = get_category_id();
                let user_name = get_user_name();
                let cat_name = get_category_name();

                console.log("Cat :", cat_id, "User :", user_id, "Username :", user_name, "Catname :", cat_name);

                if (cat_id !== 0 && user_id !== 0) {
                    get_user_items(cat_id, user_id);
                }
            }
        </script>
    </body>
</html>