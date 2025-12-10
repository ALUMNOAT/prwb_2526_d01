<?php

require_once 'model/Member.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';

class ControllerMember extends Controller {

    // page d'accueil (à adapter)
    public function index() : void {
        $this->profile();
    }

    // profil de l'utilisateur connecté ou donné (à adapter)
    public function profile() : void {
        $member = $this->get_user_or_redirect();
        if (isset($_GET["param1"]) && $_GET["param1"] !== "") {
            $member = Member::get_member_by_pseudo($_GET["param1"]);
        }
        (new View("profile"))->show(["member" => $member]);
    }

    public function change_password() : void {
        $member = $this->get_user_or_redirect();
        $errors = [];
        $success = "";

        if (isset($_POST["current_password"], $_POST["new_password"], $_POST["confirm_password"])
            && $_POST["current_password"] !== "" && $_POST["new_password"] !== "" && $_POST["confirm_password"] !== "") {

            $current_password = $_POST["current_password"];
            $new_password = $_POST["new_password"];
            $confirm_password = $_POST["confirm_password"];

            // On est connecté, donc on a toutes les données de la session, donc autant les utiliser (on a l'email)
            $errors = array_merge($errors, Member::validate_login($member->get_email(), $current_password));
            $errors = array_merge($errors, Member::validate_passwords($new_password, $confirm_password));

            if (count($errors) == 0){
                $member->set_password(password_hash($new_password, PASSWORD_BCRYPT));
                $member->persist();
                $success = "Password updated successfully!";

            }
        }


        (new View("change_password"))->show(["member" => $member, "errors" => $errors, "success" => $success]);
    }


}
