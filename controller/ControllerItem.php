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

    public function add_edit_item(): void {

        $member = $this->get_user_or_redirect();

        $item = null;
        $errors = [];

        // On check si le Param1 existe
        $id = isset($_GET['param1']) ? (int)$_GET['param1'] : null;

        if ($id) {
            // On fetch l'ID de la DB
            $item = Item::get_item($id);
            // Si l'ID est mauvais (existe pas)-> redirect
            if (!$item) {
                $this->redirect("browse");
            }
            // Si c'est pas le propriétaire qui modif -> redirect
            if ($item->get_owner()->get_id() !== $member->get_id()) {
                $this->redirect("browse");
            }
        }

        // If form submitted
        if (!empty($_POST)) {
            // Fetch data
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? null;
            $duration_days = isset($_POST['days']) ? (int)$_POST['days'] : 7;

            $starting_bid = isset($_POST['starting_bid']) && $_POST['starting_bid'] !== ''
                ? (float)$_POST['starting_bid']
                : null;

            $buy_now_price = isset($_POST['buy_now_price']) && $_POST['buy_now_price'] !== ''
                ? (float)$_POST['buy_now_price']
                : null;

            $sale_price = isset($_POST['sale_price']) && $_POST['sale_price'] !== ''
                ? (float)$_POST['sale_price']
                : null;

            // Si sale_price existe et reste est null, alors pas de bid et buy_now_price = $sale_price
            if ($sale_price !== null && $starting_bid === null && $buy_now_price === null) {
                $buy_now_price = $sale_price;
                $starting_bid = null;
            }


            // Si on fetch un item existant, alors l'item existe et on modif les données
            // Sinon, il existe pas et il est null, du coup on crée l'item
            if (!$item) {
                $item = new Item(
                    null,
                    $title,
                    $member,            // owner
                    AppTime::get_current_datetime(), // created_at
                    $duration_days,
                    [],                 // pictures
                    $description,
                    $buy_now_price,
                    $starting_bid
                );
            } else {
                // Update existing item fields
                $item->set_title($title);
                $item->set_description($description);
                $item->set_duration_days($duration_days);
                $item->set_starting_bid($starting_bid);
                $item->set_buy_now_price($buy_now_price);
            }

            // Validate
            $errors = array_merge(
                $item->validate_title(),
                $item->validate_bid(),
                $item->validate_ipp()
            );

            // If valid, persist and redirect
            if (empty($errors)) {
                $item->persist();
                $this->redirect("item", "show", $item->get_id());
            }


        }

        // Show form
        (new View("add_edit_item"))->show([
            "member" => $member,
            "item" => $item,
            "errors" => $errors
        ]);
    }




}
