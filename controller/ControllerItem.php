<?php
require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/ItemStatus.php";

class ControllerItem extends Controller {

    public function index(): void {
        $this->redirect();
    }

    public function show(): void {
        $member = $this->get_user_or_redirect();

        // récupérer l'ID depuis GET
        $id = isset($_GET['param1']) ? (int)$_GET['param1'] : null;
        // si pas d'ID -> rediriger vers browse
        if (!$id) $this->redirect("browse");

        $item_status = ItemStatus::get_item_status((int)$id);
        if(!$item_status) $this->redirect("browse");

        $item = $item_status->get_item();

        // On prend le plus grand bid, ou le bid initial si pas de bids
        $currentBid = $item_status->get_max_bid(); //float|null
        if ($currentBid === null) {
            $currentBid = $item->get_starting_bid();
        }

        (new View("item"))->show(["member" => $member, "item" => $item_status->get_item(), "item_status" => $item_status, "currentBid" => $currentBid]);
    }
}
