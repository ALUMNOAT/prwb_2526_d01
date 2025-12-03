<?php
require_once "framework/Controller.php";

class ControllerBrowse extends Controller {
    public function index(): void {
        (new View("browse"))->show([]);
    }
}