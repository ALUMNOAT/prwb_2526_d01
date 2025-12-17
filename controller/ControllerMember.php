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

    public function edit_profile() : void {
        $member = $this->get_user_or_redirect();
        $errors = [];
        $success = "";

        if (isset($_POST["full_name"], $_POST["pseudo"], $_POST["email"])
            && $_POST["full_name"] !== "" && $_POST["pseudo"] !== "" && $_POST["email"] !== "") {

            $full_name = $_POST["full_name"];
            $pseudo = $_POST["pseudo"];
            $email = $_POST["email"];
            $iban = $_POST["iban"] ?? null;
            // Pour avoir NULL dans la BD (On économise 8x plus d'espace memoire, 0,125 octet = NULL)
            if ($iban === ""){
                $iban = null;
            }

            /** On vérifie que le pseudo, email et full_name sont uniques ainsi que les règles métier
             *
             * On ne veut rien vérifier si on ne change rien (données formulaire = données de la Session)
             *
             * Explication: On change un champ, on lance les vérifs
             *
             * validate_unicity() ne valide pas l'unicité si l'ID qu'il trouve dans la DB match notre ID
             *
             * Explication: Je suis Emilian, je change pas mon nom mais mon iban, je veux pas avoir "Emilian" est pris, mon adresse email est prise, ... alors que je suis moi
             */

            if($pseudo !== $member->get_pseudo() || $email !== $member->get_email() ||
                $full_name !== $member->get_full_name() || $iban !== $member->get_iban()) {

                // On vérifie que le pseudo, email et full_name sont uniques
                $errors = Member::validate_unicity($pseudo, $email, $full_name, $member->get_id());
                // On vérifie les règles métiers du full_name
                $errors = array_merge($errors, $member->validate_full_name());
                // On vérifie les règles métiers du pseudo
                $errors = array_merge($errors, $member->validate_pseudo());
                // On vérifie les règles métiers du email
                $errors = array_merge($errors, $member->validate_email());
                // On vérifie la validité de l'IBAN (s'il existe)
                if ($iban !== null) {
                    $errors = array_merge($errors, $member->validate_iban($iban));
                }
            }

            if (count($errors) == 0){
                $member->set_full_name($full_name);
                $member->set_pseudo($pseudo);
                $member->set_email($email);
                // On enlève les espaces de la bd (un espace = 1 octet de mémoire = 8 bits)
                // 8 bits * 3 espaces = 24 bits pour un seul IBAN en bd (OPTIMISATION)
                $member->set_iban(str_replace(" ", "",$iban));
                $member->persist();
                $success = "Profile updated successfully!";
            }

        }

        (new View("edit_profile"))->show(["member" => $member, "errors" => $errors, "success" => $success]);
    }

    public function profile_picture() : void {
        $member = $this->get_user_or_redirect();
        $errors = [];
        $success = "";

        /**
         * UPLOAD DE LA PHOTO avec button "Upload Picture"
         */

        if (isset($_POST['upload'])) {
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $errors = Member::validate_photo($_FILES['image']);
                if (empty($errors)) {

                    // Dossier utilisateur
                    $userDir = "uploads/users/" . $member->get_id();
                    // si il n'y a pas de dossier, on le crée
                    if (!is_dir($userDir)) {
                        // Permission : owner peut r-w-x, group peut r-x, reste peut r-x
                        mkdir($userDir, 0755, true);
                    }

                    // Nom du fichier
                    $ext = Member::get_photo_extension($_FILES['image']);
                    $relativePath = $userDir . "/profile." . $ext;

                    // Suppression ancienne photo (uniquement la photo de la BD)
                    if ($member->get_picture_path() && file_exists($member->get_picture_path())) {
                        unlink($member->get_picture_path());
                    }

                    // Upload
                    move_uploaded_file($_FILES['image']['tmp_name'], $relativePath);

                    // DB -> chemin relatif
                    $member->set_picture_path($relativePath);
                    $member->persist();
                    $success = "Profile picture uploaded successfully!";
                }
            } else {
                $errors[] = "Upload failed!";
            }
        }

        /**
         * SUPPRESSION DE LA PHOTO avec button "Delete Picture"
         */

        elseif (isset($_POST['delete'])) {
            if ($member->get_picture_path() && file_exists($member->get_picture_path())) {
                unlink($member->get_picture_path());
            }
            $member->set_picture_path(null);
            $member->persist();

            $success = "Your profile picture has been successfully deleted.";
        }

        /**
         * LE RESTE EST COMMUN POUR LES 2 BOUTONS
         *
         * UPDATE DU PROFIL
         */

        if (isset($_POST["profile"])) {
            $member->profile = $_POST['profile'];
            $member->persist();
            $success = "Your profile has been successfully updated.";
        }

        /**
         * REDIRECTION POST
         */

        if (!empty($_POST) && empty($errors)) {
            $this->redirect("member", "profile_picture", "ok");
        }

        /**
         * AFFICHAGE DU MESSAGE DE SUCCES
         */

        if (isset($_GET['param1']) && $_GET['param1'] === "ok") {
            $success = "Your profile has been successfully updated.";
        }

        (new View("profile_picture"))->show(["member" => $member, "errors" => $errors, "success" => $success]);

    }


}
