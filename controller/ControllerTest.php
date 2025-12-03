<?php
require_once "framework/Controller.php";
require_once "model/Member.php";


class ControllerTest extends Controller {
    public function index(): void {
        $test = new Member("test_name", "test@test.com", "testy", "Dummy123,", Role::USER, 0); 
        (new View("test"))->show(["member" => $test]);
    }
}