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
        if ($this->user_logged()) {
            /**
             * @var Member $user
             */
            $user = $this->get_user_or_redirect();
            $my_bids = $user->get_my_bids(); //returns '[]' for guests
            $available_items = Item::get_all_available_items();

            // set theory: 'A - B' on keys (here it's item id)
            $my_available_items = array_diff_key($available_items, $my_bids);

            (new View("browse"))->show([
                "user" => $user,
                "my_bids" => $my_bids,
                "available_items" => $my_available_items
            ]);
        } else
            $this->redirect('login');
    }
}
