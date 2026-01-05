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
        $user = $this->get_user_or_redirect_guess();
        $item_id = $this->get_id_or_redirect($_GET['param1']);
        $item = $this->get_item_or_redirect($item_id, $user);

        (new View("manage_images"))->show(["item" => $item]);
    }

    public function remove_image(): void {
        $user = $this->get_user_or_redirect_guess();
        $item_id = $this->get_id_or_redirect($_GET['param1']);
        $picture_priority = $this->get_id_or_redirect($_POST['priority'], 'item', 'manage_images', $item_id);
        $item = $this->get_item_or_redirect($item_id, $user);
        $item_picture = $this->get_item_picture_or_redirect($item, $picture_priority);

        $item_picture->delete($user);
        $this->redirect("item", "manage_images", strval($item_id));
    }

    public function move_image(): void {
        /*
        doc: https://beerpla.net/2009/02/17/swapping-column-values-in-mysql/
        doc: https://stackoverflow.com/questions/5029409/how-to-check-if-an-integer-is-within-a-range <-- sometimes simplicity is "ugly", but faster
        */
        $user = $this->get_user_or_redirect_guess();
        $item_id = $this->get_id_or_redirect($_GET['param1']);
        $priorities = isset($_POST['priority'])
            ? explode(",", $_POST['priority'])
            : [false, false];
        $picture_priority = $this->get_id_or_redirect($priorities[0], 'item', 'manage_images', $item_id);
        $target_priority = $this->get_id_or_redirect($priorities[1] ?? false, 'item', 'manage_images', $item_id);
        $item = $this->get_item_or_redirect($item_id, $user);
        $item_picture = $this->get_item_picture_or_redirect($item, $picture_priority);
        $target_item_picture = $this->get_item_picture_or_redirect($item, $target_priority);

        $item_picture->swap_priority($target_item_picture);
        $this->redirect("item", "manage_images", strval($item_id));
    }

    private function get_user_or_redirect_guess(bool $guest_pass = false): Member {
        /** @var Member $user */
        $user = $this->get_user_or_redirect();
        if ($user->is_guest() && !$guest_pass)
            $this->redirect('browse');
        return $user;
    }

    private function get_id_or_redirect(mixed $id, string $controller = 'browse', string $action = 'page', string $param1 = '1'): int {
        if (!isset($id) || !NumberConverter::is_id($id))
            $this->redirect($controller, $action, $param1);
        $id = NumberConverter::get_id_or_false($id);
        return $id;
    }

    private function get_item_or_redirect(int $item_id, ?Member $user = null): Item {
        $item = Item::get_item($item_id);
        if (!$item || $user !== null && !$item->is_owner($user))
            $this->redirect("browse", "page", "1");
        return $item;
    }

    private function get_item_picture_or_redirect(Item $item, int $pic_priority): ItemPicture {
        $pic = $item->get_picture($pic_priority);
        if (!$pic)
            $this->redirect("item", "manage_images", strval($item->get_id()));
        return $pic;
    }
}
