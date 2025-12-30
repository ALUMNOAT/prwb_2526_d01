<?php
require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/ItemStatus.php";
require_once 'model/Bid.php';

class ControllerItem extends Controller {

    public function index(): void {
        $this->redirect();
    }

    public function show(): void {
        $member = $this->get_user_or_redirect();
        $errors = [];
        $success = "";

        $id = isset($_GET['param1']) ? (int)$_GET['param1'] : null;
        if (!$id) $this->redirect("browse");

        $item_status = ItemStatus::get_item_status($id);
        if (!$item_status) $this->redirect("browse");

        $item = $item_status->get_item();

        $currentBid = $item_status->get_max_bid();
        if ($currentBid === null) $currentBid = $item->get_starting_bid();

        $bids = Bid::get_bids_for_item($item->get_id());

        $pictures = $item->get_pictures();
        $mainPic = $pictures[0] ?? null;

        // If a second parameter exists (clean URL image), override mainPic
        if (!empty($_GET['param2']) && !empty($pictures)) {
            $imgName = urldecode($_GET['param2']);
            foreach ($pictures as $pic) {
                $picName = pathinfo($pic->get_picture_path(), PATHINFO_BASENAME);
                if ($picName === $imgName) {
                    $mainPic = $pic;
                    break;
                }
            }
        }

        if (isset($_POST["bid"])) {
            $bidAmount = isset($_POST['make_bid']) ? (float)$_POST['make_bid'] : null;
            $errors = Bid::validate_bid($member, $item_status, $bidAmount);

            if (empty($errors)) {
                Bid::place_bid($member, $item_status, $bidAmount);
                $success = "You bidded successfully!";
            }
        }

        if (isset($_POST["buy"])) {
            $buyNowPrice = $item->get_buy_now_price() ?? 0;
            $currentBid = $item_status->get_max_bid() ?? $item->get_starting_bid();

            $errors = [];

            if (!$item_status->get_has_buy_now() || $item_status->get_buy_now_reached() || $buyNowPrice <= $currentBid) {
                $errors[] = "Cannot buy this item now.";
            } else {
                Bid::place_bid($member, $item_status, $buyNowPrice);
                $success = "You bought the item successfully!";
            }
        }

        // Redirect if POST and no errors (PRG)
        if (!empty($_POST) && empty($errors)) {
            $this->redirect("item", "show", $item->get_id());
        }


        (new View("item"))->show([
            "member" => $member,
            "item" => $item,
            "item_status" => $item_status,
            "currentBid" => $currentBid,
            "bids" => $bids,
            "mainPic" => $mainPic,
            "pictures"=>$pictures,
            "errors" => $errors,
            "success" => $success
        ]);
    }

}
