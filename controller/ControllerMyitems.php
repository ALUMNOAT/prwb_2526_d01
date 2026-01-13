<?php
require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/ItemStatus.php";
require_once "utils/NumberConverter.php";


class ControllerMyitems extends Controller {
    public function index(): void {
        (new View('my_items'))->show();
    }
}