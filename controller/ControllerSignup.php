<?php
require_once "framework/Controller.php";
require_once "framework/View.php";
require_once "model/Member.php";


class ControllerSignup extends Controller {
    public function index(): void {
        if ($this->user_logged()) {
            $user = $this->get_user_or_false();
            if ($user && $user->get_id() != null)
                $this->redirect("item", "browse");
        }

        $email = "";
        $full_name = "";
        $pseudo = "";
        $password = "";
        $password_confirm = "";
        $errors = [];

        if (isset($_POST["email"]) && isset($_POST["full_name"]) && isset($_POST["pseudo"]) && isset($_POST["password"]) && isset($_POST["password_confirm"])) {
            $email = html_entity_decode($_POST["email"], ENT_QUOTES, 'UTF-8');
            $full_name = html_entity_decode($_POST["full_name"], ENT_QUOTES, 'UTF-8');
            $pseudo = html_entity_decode($_POST["pseudo"], ENT_QUOTES, 'UTF-8');
            $password = html_entity_decode($_POST["password"], ENT_QUOTES, 'UTF-8');
            $password_confirm = html_entity_decode($_POST["password_confirm"], ENT_QUOTES, 'UTF-8');

            $member = new Member(null, $full_name, $email, $pseudo, password_hash($password, PASSWORD_BCRYPT), Role::USER);
            // On vérifie que le pseudo et email sont uniques
            $errors = Member::validate_unicity($pseudo, $email, $full_name);
            // On vérifie les règles métiers du full_name
            $errors = array_merge($errors, $member->validate_full_name());
            // On vérifie les règles métiers du pseudo
            $errors = array_merge($errors, $member->validate_pseudo());
            // On vérifie les règles métiers du email
            $errors = array_merge($errors, $member->validate_email());
            // On vérifie d'abord les règles metiers du mot de passe récursivement (validate_passwords() appelle validate_password())
            // Puis, on vérifie si les 2 mots de passe sont identiques
            $errors = array_merge($errors, Member::validate_passwords($password, $password_confirm));

            if (count($errors) == 0) {
                // update la base de données
                $member->persist();
                // login
                $this->log_user($member, "item", "browse");
            }
        }

        if(isset($user))
            (new View("signup"))->show(["user"=>$user,"email" => $email, "full_name" => $full_name, "pseudo" => $pseudo, "password" => $password, "password_confirm" => $password_confirm, "errors" => $errors]);
        else 
            (new View("signup"))->show(["email" => $email, "full_name" => $full_name, "pseudo" => $pseudo, "password" => $password, "password_confirm" => $password_confirm, "errors" => $errors]);
    }

    public function logout_guest() : void {
        parent::logout();
    }
}
