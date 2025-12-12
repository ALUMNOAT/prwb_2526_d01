<?php
require_once "framework/Controller.php";
require_once "framework/View.php";
require_once "model/Member.php";


class ControllerTest extends Controller {

    public function index(): void
    {
        if ($this->user_logged()) {
            // Récupère le membre connecté depuis la session
            $member = $this->get_user_or_false();
            (new View("test"))->show(["member" => $member]);
        } else {
            // Membre de test si personne n'est connecté
            $test = new Member("99999", "Testy Testy", "test@test.com", "testy", "Dummy123,", Role::USER);
            (new View("test"))->show(["member" => $test]);
        }
    }

    // gestion de la connection d'un utilisateur
    public function login(): void {
        $email = "";
        $password = "";
        $errors = [];

        if (isset($_POST["email"]) && isset($_POST["password"])) {
            $email = $_POST["email"];
            $password = $_POST["password"];

            $errors = Member::validate_login($email, $password);
            if (empty($errors)) {
                $member = Member::get_member_by_email($email);
                if ($member !== false) { // s'assure que le membre existe
                    $this->log_user($member); // stocke le membre connecté dans la session
                }
            }
        }
        (new View("login"))->show(["email" => $email, "password" => $password, "errors" => $errors]);
    }

    public function signup(): void {
        $email = "";
        $full_name = "";
        $pseudo = "";
        $password = "";
        $password_confirm = "";
        $errors = [];

        if (isset($_POST["email"]) && isset($_POST["full_name"]) && isset($_POST["pseudo"]) && isset($_POST["password"]) && isset($_POST["password_confirm"])) {
            $email = trim($_POST["email"]);
            $full_name = trim($_POST["full_name"]);
            $pseudo = trim($_POST["pseudo"]);
            $password = $_POST["password"];
            $password_confirm = $_POST["password_confirm"];

            $member = new Member(null, $full_name, $email, $pseudo, password_hash($password, PASSWORD_BCRYPT), Role::USER);
            // On vérifie que le pseudo, email et full_name sont uniques
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
                $this->log_user($member);
            }
        }
        (new View("signup"))->show(["email"=>$email, "full_name"=>$full_name, "pseudo"=>$pseudo, "password"=>$password, "password_confirm"=>$password_confirm, "errors"=>$errors]);
    }

    public function login_as(): void {
        if (!isset($_GET['email'])) {
            // pas d'email fourni, on peut rediriger ou afficher une erreur
            header("Location: index.php?controller=test&action=index");
            exit;
        }

        $email = $_GET['email'];
        $member = Member::get_member_by_email($email);
        if ($member !== false) {
            $this->log_user($member);
        }

        header("Location: index.php?controller=test&action=index");
        exit;
    }

    public function logout(): void {
        if ($this->user_logged()) {
            parent::logout();
        }
    }


}