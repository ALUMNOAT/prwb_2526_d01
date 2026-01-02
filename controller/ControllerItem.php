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
}
