<?php
require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/ItemStatus.php";


class ControllerBrowse extends Controller {
    public function index(): void {
        /*
        doc: https://www.php.net/manual/en/function.array-intersect-key.php
        doc: https://www.php.net/manual/en/function.array-diff.php
        */
        $user = Member::get_member(1);
        $my_bidded_items = $user->get_my_bidded_items();
        $available_items = Item::get_all_available_items();
        $my_available_items = array_diff_key($available_items, $my_bidded_items); // set theory: A - B on keys (here it's item's id)
        (new View("browse"))->show([
            "my_bidded_items" => $my_bidded_items,
            "available_items" => $my_available_items
        ]);
    }
}
