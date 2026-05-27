<?php

require_once 'model/Member.php';
require_once 'model/Categories.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';
require_once 'utils/Role.php';

class ControllerCategory extends Controller
{

    // page d'accueil
    public function index(): void
    {
        $this->category();
    }

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

                if (empty($errors)) {
                    $errors = array_merge(
                        $errors,
                        $category->validate_name_length(),
                        $category->validate_category_unicity()
                    );
                }

                if (empty($errors)) {
                    $category->persist();
                    $this->redirect("category");
                }
            }

            if ($action === "save" && $id) {
                $category = Categories::get_one((int) $id);
                if ($category) { // si existe
                    // On update le nom de l'objet par la valeur du POST
                    $newName = $_POST["name"];
                    $category->set_name($newName);

                    // On valide
                    if (empty($errors)) {
                        $errors = array_merge(
                            $errors,
                            $category->validate_name_length(),
                            $category->validate_category_unicity()
                        );
                    }

                    // Si valide, persist et redirect
                    if (empty($errors)) {
                        $category->persist();
                        $this->redirect("category");
                    }
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

        (new View("manage_categories"))->show([
            "user" => $member,
            "all_categories" => $categories,
            "errors" => $errors
        ]);
    }

    public function update_order_service(): void
    {
        $this->get_admin_or_redirect();
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if ($data && isset($data['order'])) {
            // 1. ÉTAPE TAMPON : On met toutes les catégories à une priorité négative 
            // pour libérer toutes les valeurs de 1 à N dans la base de données.
            foreach ($data['order'] as $item) {
                $category = Categories::get_one((int) $item['id']);
                if ($category) {
                    // On utilise une valeur temporaire unique pour chaque ID pour éviter la collision
                    $category->set_priority(-((int) $item['id']));
                    $category->persist();
                }
            }

            // 2. ÉTAPE FINALE : On applique les nouvelles priorités maintenant que tout est libre.
            foreach ($data['order'] as $item) {
                $category = Categories::get_one((int) $item['id']);
                if ($category) {
                    $category->set_priority((int) $item['priority']);
                    $category->persist();
                }
            }

            header('Content-Type: application/json');
            //debug en réponse
            //echo json_encode(['status' => 'debug', 'received' => $data]);
            //exit;
            echo json_encode(['status' => 'success']);
            exit;
        }
    }

    public function validate_categories_service(): void
    {
        $this->get_admin_or_redirect();

        // Récupérer la valeur saisie dynamiquement
        // PAS DE FETCH DE LA BASE DE DONNEES, sinon c'est pas dynamique
        $name = $_POST["name"] ?? $_POST["new_category"] ?? "";
        $id = $_POST["category_id"] ?? null;

        // On crée une catégorie
        $categorie = new Categories(
            $id,
            $name,
            0
        );

        // Vérification locale
        $errors = $categorie->validate_name_length();

        // Vérif BDD
        if (empty($errors)) {
            $errors = array_merge(
                $errors,
                $categorie->validate_category_unicity()
            );
        }

        header('Content-Type: application/json');

        echo json_encode([
            'debug_input' => [
                'id' => $id,
                'nom' => $categorie->get_name(),
                'priorité' => $categorie->get_priority()
            ],
            'errors' => $errors
        ], JSON_PRETTY_PRINT);

        exit;
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