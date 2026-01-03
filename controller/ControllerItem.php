<?php
require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/ItemStatus.php";
require_once "utils/NumberConverter.php";

class ControllerItem extends Controller {

    public function index(): void {
        $this->redirect();
    }

    public function show(): void {
        $member = $this->get_user_or_redirect();

        // récupérer l'ID depuis GET
        $id = isset($_GET['param1']) ? (int)$_GET['param1'] : null;

        if (!$id) {
            // si pas d'ID -> rediriger vers browse
            $this->redirect("browse");
        }

        $item_status = ItemStatus::get_item_status((int)$id);

        if (!$item_status) {
            $this->redirect("browse");
        }

        (new View("item"))->show(["member" => $member, "item" => $item_status->get_item(), "item_status" => $item_status]);
    }

    public function manage_images(): void {
        if (!$this->user_logged())
            $this->redirect('login');

        /** @var Member $user */
        $user = $this->get_user_or_redirect();

        if($user->is_guest())
            $this->redirect('browse');
        elseif (!isset($_GET['param1']) || !NumberConverter::is_id($_GET['param1']))
            $this->redirect('browse', 'page', '1'); //this will be changed to redirect towards my_items later on
        else {
            $item_id = NumberConverter::get_id_or_false($_GET['param1']);
            $item = Item::get_item($item_id);
            (new View("manage_images"))->show(["item" => $item]);
        }
    }

    public function remove_image(): void {
        $user = $this->get_user_or_redirect_guess();
        $item_id = $this->get_id_or_redirect($_GET['param1']);
        $picture_priority = $this->get_id_or_redirect($_POST['priority'], 'item', 'manage_images', $item_id);
        
        $item = Item::get_item($item_id);
        if(!$item)
            $this->redirect("browse", "page", "1");
        else {
            $item_picture = $item->get_picture($picture_priority);

            if(!$item_picture)
                $this->redirect("item", "manage_images_1if", strval($item_id));
            elseif(!$item_picture->delete($user))
                $this->redirect("browse", "page", "1");
            else
                $this->redirect("item", "manage_images", strval($item_id));
        }
    }
    
    public function move_image_up(): void {
        
    }

    public function remove_image_down(): void {
        
    }

    private function get_user_or_redirect_guess(bool $guest_pass = false): Member {
        /** @var Member $user */
        $user = $this->get_user_or_redirect();
        if($user->is_guest() && !$guest_pass)
            $this->redirect('browse');
        return $user;
    }

    private function get_id_or_redirect(mixed $id, string $controller = 'browse', string $action = 'page', string $param1 = '1'): int {
        if (!isset($id) || !NumberConverter::is_id($id))
            $this->redirect($controller, $action, $param1);
        $id = NumberConverter::get_id_or_false($id);
        return $id;
    }
}
