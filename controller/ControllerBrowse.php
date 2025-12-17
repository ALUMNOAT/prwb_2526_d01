<?php
require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/ItemStatus.php";


class ControllerBrowse extends Controller {
    public function index(): void {
        //$user = $this->get_user_or_redirect();
        $user = Member::get_member(1);
        (new View("browse"))->show([
            "my_bidded_items" => $user->get_my_bidded_items()
        ]);
    }
}