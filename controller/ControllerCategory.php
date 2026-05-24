<?php

require_once 'model/Member.php';
require_once 'model/Categories.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';
require_once 'utils/Role.php';

class ControllerCategory extends Controller
{

    // page d'accueil (à adapter)
    public function index(): void
    {
        $this->category();
    }

    // profil de l'utilisateur connecté ou donné (à adapter)
    public function category(): void
    {
        $member = $this->get_admin_or_redirect();
        $categories = Categories::get_all_categories_sorted_priority();
        $errors = [];

        if (isset($_POST["action"])) {
            $action = $_POST["action"];
            $id = $_POST["category_id"] ?? null;

            if ($action === "delete" && $id) {
                $category = Categories::get_one((int) $id);
                if ($category && !$category->isUsed()) { // si existe et non utilisé
                    $category->delete();
                    $this->redirect("category");
                }
            }

            if ($action === "add") {
                $name = $_POST["new_category"];
                $category = new Categories(null, $name, 1000);
                $category->persist();
                $this->redirect("category");
            }

            if ($action === "save" && $id) {
                $category = Categories::get_one((int) $id);
                if ($category) { // si existe et non utilisé
                    // On update le nom de l'objet par la valeur du POST
                    $newName = $_POST["name"];
                    $category->set_name($newName);
                    $category->persist();
                    $this->redirect("category");
                }
            }

            if ($action === "up" || $action === "down") {
                $category = Categories::get_one((int) $id);
                if ($category) {
                    $currentPriority = $category->get_priority();

                    // Trouver le voisin
                    $targetPriority = ($action === "up") ? ($currentPriority - 1) : ($currentPriority + 1);
                    $neighbor = Categories::get_by_priority($targetPriority);

                    if ($neighbor) {
                        // Swap : On utilise 0 ou -1 pour éviter les doublons de priorité temporairement
                        $category->set_priority(-1);
                        $category->persist();

                        $neighbor->set_priority($currentPriority);
                        $neighbor->persist();

                        $category->set_priority($targetPriority);
                        $category->persist();

                        $this->redirect("category");
                    }
                }
            }
        }

        (new View("manage_categories"))->show(["user" => $member, "all_categories" => $categories]);
    }

    private function get_admin_or_redirect(bool $guest_pass = false): Member
    {
        /** @var Member $user */
        $user = $this->get_user_or_redirect();
        if (!$user->is_admin() && !$guest_pass)
            $this->redirect("item", "browse");
        return $user;
    }
}