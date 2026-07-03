<!DOCTYPE html>
<html>
    <head>
        <base href="<?= $web_root ?>">
    </head>
    <body>
        <div>
            <form method="POST" action="training/selected_category">
                <div id="Categories">
                    <h6>Category</h6>
                    <div>
                        <select name = "selected_category">
                            <option value="0">Choose a category</option>

                            <?php foreach($category as $cat):
                            $cat_id = $cat->get_id();
                            $cat_name =$cat->get_name();
                            ?>

                            <option value="<?= $cat_id ?>" 
                            <?php if ($selected_category == $cat_id):
                                echo "selected"; ?>
                                <?php endif; ?>>

                            <?= $cat_name ?></option>
                            
                            <?php endforeach ?>
                        </select>
                        <button type="submit">OK</button>
                    </div>
                </div>
            </form>

            <?php if(isset($selected_category) && $selected_category != 0) : ?>
            <div id="Users">
                <h6>User</h6>
                <div>
                    <select name = "user_selected" onchange="get_user_items()">
                        <option value="0">Choose a user</option>

                        <?php foreach($users as $user):
                        $user_id = $user->get_id();
                        $user_name = $user->get_full_name();
                        $user_pseudo = $user->get_pseudo();
                        ?>

                        <option value="<?= $user_id ?>">
                        <?= $user_pseudo . " (" . $user_name . ")" ?></option>

                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div id="Items">
                
            </div>
        </div>
        <?php endif; ?>

        <script>

            let user_id, cat_id, user_name, cat_name;

            function get_user_id() {
                let user_container = document.querySelector('select[name="user_selected"]');
                user_id = user_container.value;
                console.log("user: " + user_id);
                return user_id;
            }

            function get_category_id() {
                let category_container = document.querySelector('select[name="selected_category"]');
                cat_id = category_container.value;
                console.log("cat: " + cat_id);
                return cat_id;
            }

            function get_user_name() {
                let user_container = document.querySelector('select[name="user_selected"]');
                user_name = user_container.options[user_container.selectedIndex].text;
                console.log("username: " + user_name);
                return user_name;
            }

            function get_category_name() {
                let category_container = document.querySelector('select[name="selected_category"]');
                cat_name = category_container.options[category_container.selectedIndex].text;
                console.log("category_name: " + cat_name);
                return cat_name;
            }

            async function get_user_items() {
                get_user_id();
                get_category_id();
                try {
                    const response = await fetch("training/get_user_items_service/"+cat_id+"/"+user_id, {
                        method: "GET",
                        headers: {'Content-Type': 'application/json'},
                    });

                    const result = await response.json();
                    if (result.status == 'success') {
                        affichage_des_items(result.items);
                        console.log(result.items);
                    }
                } catch (error) {
                    console.error("Erreur lors du fetch :" + error);
                }
            }

            function affichage_des_items(items) {
                get_user_name();
                get_category_name();
                let items_container = document.getElementById("Items");
                items_container.innerHTML = ""; // on le reset;

                let html = "";

                html += `<h6>Items from ${user_name} with category: ${cat_name}</h6>`;

                for ([id, title] of Object.entries(items)) {
                    html += 
                    `<div>
                        <input value="${id}" name="items[]" type="checkbox" onclick="update_button()">
                        ${title}
                    </div>`;
                }

                html += `<button type="button" id="remove-btn" onclick="delete_categorie_from_items()" disabled>Remove category</button>`;

                items_container.innerHTML = html;
            }

            function update_button() {
                let button_container = document.getElementById("remove-btn");
                let items_container = document.querySelectorAll('input[name="items[]"]:checked');

                if (items_container.length === 0) {
                    button_container.disabled = true;
                } else {
                    button_container.disabled = false;
                }
            }

            function deletecheckedboxes() {
                let items_checked = document.querySelectorAll('input[name="items[]"]:checked');

                items_checked.forEach((checkbox) => {
                    checkbox.parentElement.remove();
                });

                update_button();
            }

            async function delete_categorie_from_items() {
                let cat_id = get_category_id();
                let items_list_html = document.querySelectorAll('input[name="items[]"]:checked');
                let items_list = [];

                items_list_html.forEach(element => {
                    let id = element.value;
                    items_list.push(id);
                });

                try {
                    const response = await fetch("training/delete_categorie_from_items_service/",
                    {
                        method: "POST",
                        headers: {'Content-Type' : 'application/json'},
                        body: JSON.stringify({
                            cat_id : cat_id,
                            items_id : items_list 
                        })
                    });

                    const result = await response.json();

                    if (result.status === "success") {
                        deletecheckedboxes();
                    }
                } catch (error) {
                    console.error(error);
                }
            }

        </script>
    </body>
</html>