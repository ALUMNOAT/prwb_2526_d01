<?php
require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/ItemStatus.php";
require_once "utils/NumberConverter.php";
require_once 'utils/Image.php';
require_once 'model/Bid.php';
require_once 'utils/UrlDataEncoderDecoder.php';
require_once "model/ItemCategories.php";
require_once "model/Categories.php";
require_once "model/Member.php";

class ControllerExam extends Controller
{

    public function index(): void
    {
        $this->get_admin_or_redirect();
        $categories = Categories::get_all_categories();
        $selected_category = $_GET["param1"] ?? 0;
        $errors = [];

        if (isset($selected_category)) {
            $users = Member::get_users_using_category($selected_category);
        }

        (new View("exam"))->show(
            ["categories" => $categories,
            "selected_category" => $selected_category,
            "users" => $users]
        );
    }

    public function selected_category_post(): void {
        $this->get_admin_or_redirect();
        $selected_category_post = $_POST["category_selected"];
        $errors = [];

        if (empty($errors)) {
            $this->redirect("exam", "index", $selected_category_post);
        }
    }

    public function user_get_items_category_service(): void {
        $this->get_admin_or_redirect();
        $selected_category_id = $_GET["param1"] ?? 0;
        $selected_user_id = $_GET["param2"] ?? 0;

        $items = Item::items_having_category($selected_category_id, $selected_user_id);
        
        header('Content-Type: application/json');

        echo json_encode([
            'status' => 'success',
            'items' => $items
        ]);

        exit;
    }

    public function delete_category_of_an_item_service(): void {
        $this->get_admin_or_redirect();

        $json_data = file_get_contents('php://input');

        $data = json_decode($json_data, true);

        $selected_category_id = $data["cat_id"] ?? 0;
        $selected_items_id = $data["items_id"] ?? null;

        foreach ($selected_items_id as $id) {
            ItemCategories::delete_category_of_an_item($id, $selected_category_id);
        }

        header('Content-Type: application/json');

        echo json_encode([
            'status' => 'success',
            'cat_id' => $selected_category_id,
            'items_id' => $selected_items_id
        ]);

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

?>