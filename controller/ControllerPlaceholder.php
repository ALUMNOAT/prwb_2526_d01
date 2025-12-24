<?php
require_once "framework/Controller.php";
require_once "framework/View.php";
require_once "model/Member.php";


class ControllerPlaceholder extends Controller {
    public function index(): void {
        if ($this->user_logged()) {
            $member = $this->get_user_or_false();
            (new View("test"))->show(["member" => $member]);
        } else
            $this->redirect("login");
    }

    public function logout_user(): void {
        $this->logout();
    }
}
