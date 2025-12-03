<?php
require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/ItemStatus.php";


class ControllerBrowse extends Controller {
    public function index(): void {
        (new View("browse"))->show(["available_items" => ItemStatus::get_all_available_items()]);
    }
}