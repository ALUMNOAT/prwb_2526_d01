<?php
require_once "framework/Controller.php";
require_once "framework/View.php";
require_once "framework/Tools.php";
require_once "model/Member.php";


class ControllerLogin extends Controller {
    //Luca: if we have a __construct() we can check if a user is logged before anything else
    /*
    public function __construct() {
        //before anything, check first if a user is already logged in
        if ($this->user_logged())
            $this->redirect("testy");
    }
    */

    public function index(): void {
        if ($this->user_logged())   //already connected, redirect
            $this->redirect('item','browse');
        else                        //nobody connected, or trying connection
            $email = "";
            $password = "";
            $errors = [];

            if (isset($_POST["email"]) && isset($_POST["password"])) {
                $email = $_POST["email"];
                $password = $_POST["password"];

                $errors = Member::validate_login($email, $password);

                if (empty($errors)) {
                    $member = Member::get_member_by_email($email);

                    //Luca: $member will evaluate to 'true' if there is an object, otherwise it will be false
                    //Luca: log_user() also redirects
                    if ($member) // s'assure que le membre existe
                        $this->log_user($member, 'item','browse'); // stocke le membre connecté dans la session
                }
            }
            //Luca: the login view can now work without any variables sent if we so desire. usefull when we first land on the page
            (new View("login"))->show(["email" => $email, "password" => $password, "errors" => $errors]);
    }

    public function guest(): void {
        if ($this->user_logged())
            $this->redirect('item','browse');
        else {
            $guest = new Member(null, "guest", null, "guest", null, Role::USER, null, null);
            $this->log_user($guest, 'item','browse');
        }
    }

    public function login_as(): void {
        //DEBUG ONLY 
        if (!isset($_GET['param1'])) {
            // pas de pseudo fourni, on peut rediriger ou afficher une erreur
            $this->redirect("login");
        }

        $pseudo = $_GET['param1'];
        $member = Member::get_member_by_pseudo($pseudo);
        if ($member)
            $this->log_user($member, 'item','browse');
        else
            $this->redirect("login");
    }
}
