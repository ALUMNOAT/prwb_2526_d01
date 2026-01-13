<?php
require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/ItemStatus.php";
require_once "utils/NumberConverter.php";


class ControllerMyitems extends Controller {
    /*
    doc: https://php.watch/versions/8.1/spread-operator-string-array-keys
    */
    public function index(): void {
        $user = $this->get_user_or_redirect_guess();
        $my_items = $user->get_my_items();
        (new View('my_items'))->show(array_merge(
            ['user' => $user],
            $my_items
        ));
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
