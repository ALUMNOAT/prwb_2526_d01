<?php

require_once 'model/Member.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';
require_once 'utils/Image.php';
require_once 'utils/Role.php';

class ControllerProfile extends Controller {

    // page d'accueil (à adapter)
    public function index(): void {
        $this->profile();
    }

    // profil de l'utilisateur connecté ou donné (à adapter)
    public function profile(): void {
        $member = $this->get_user_or_redirect_guess();
        if (isset($_GET["param1"]) && $_GET["param1"] !== "") {
            $member = Member::get_member_by_pseudo($_GET["param1"]);
        }
        (new View("profile"))->show(["user" => $member]);
    }

    public function change_password(): void {
        $member = $this->get_user_or_redirect_guess();
        $errors = [];
        $success = "";

        if (
            isset($_POST["current_password"], $_POST["new_password"], $_POST["confirm_password"])
            && $_POST["current_password"] !== "" && $_POST["new_password"] !== "" && $_POST["confirm_password"] !== ""
        ) {

            $current_password = html_entity_decode($_POST["current_password"], ENT_QUOTES, 'UTF-8');
            $new_password = html_entity_decode($_POST["new_password"], ENT_QUOTES, 'UTF-8');
            $confirm_password = html_entity_decode($_POST["confirm_password"], ENT_QUOTES, 'UTF-8');

            // On est connecté, donc on a toutes les données de la session, donc autant les utiliser (on a l'email)
            $errors = array_merge($errors, Member::validate_login($member->get_email(), $current_password));
            $errors = array_merge($errors, Member::validate_passwords($new_password, $confirm_password));

            if (count($errors) == 0) {
                $member->set_password(password_hash($new_password, PASSWORD_BCRYPT));
                $member->persist();

                // Redirect après POST (principe PRG)
                $this->redirect("profile", "change_password", "ok");
                return; // on stoppe le reste du code
            }
        }

        // Si la page est chargée via GET après le redirect
        if (isset($_GET['param1']) && $_GET['param1'] === "ok") {
            $success = "Password updated successfully!";
        }

        (new View("change_password"))->show(["user" => $member, "errors" => $errors, "success" => $success]);
    }

    public function edit_profile(): void {
        /** @var Member $member */
        $member = $this->get_user_or_redirect_guess();
        $errors = [];
        $success = "";

        if (isset($_POST["full_name"], $_POST["pseudo"], $_POST["email"])) {
            $full_name = html_entity_decode($_POST["full_name"], ENT_QUOTES, 'UTF-8');
            $pseudo = html_entity_decode($_POST["pseudo"], ENT_QUOTES, 'UTF-8');
            $email = html_entity_decode($_POST["email"], ENT_QUOTES, 'UTF-8');
            $iban = $_POST["iban"] === "" ? null : $_POST["iban"];

            $member->set_full_name($full_name);
            $member->set_pseudo($pseudo);
            $member->set_email($email);
            $member->set_iban($iban);

            /** On vérifie que le pseudo, email et full_name sont uniques ainsi que les règles métier
             * On ne veut rien vérifier si on ne change rien (données formulaire = données de la Session)
             * Explication: On change un champ, on lance les vérifs
             * validate_unicity() ne valide pas l'unicité si l'ID qu'il trouve dans la DB match notre ID
             * Explication: Je suis Emilian, je change pas mon nom mais mon iban, je veux pas avoir "Emilian" est pris, mon adresse email est prise, ... alors que je suis moi
             */
            
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

            if (count($errors) == 0) {
                // On enlève les espaces de la bd (un espace = 1 octet de mémoire = 8 bits)
                // 8 bits * 3 espaces = 24 bits pour un seul IBAN en bd (OPTIMISATION)
                // Je fais comme ca pour que PHP ne rale pas (str_replace n'aime pas gerer les "null")
                if ($iban !== null) {
                    $iban = str_replace(" ", "", $iban);
                }
                $member->persist();

                // Redirect après POST (principe PRG)
                $this->redirect("profile");
                return; // on stoppe le reste du code
            }
        }

        // Si la page est chargée via GET après le redirect
        if (isset($_GET['param1']) && $_GET['param1'] === "ok") {
            $success = "Profile updated successfully!";
        }

        (new View("edit_profile"))->show(["user" => $member, "errors" => $errors, "success" => $success]);
    }

    /**
     * @throws Exception
     */
    public function profile_picture(): void {
        $member = $this->get_user_or_redirect_guess();
        $errors = [];
        $success = "";

        /**
         * UPLOAD DE LA PHOTO avec button "Upload Picture"
         */

        if (isset($_POST['upload'])) {
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $errors = Member::validate_photo($_FILES['image']);
                if (empty($errors)) {

                    // Ancien chemin avant modification
                    $oldPath = $member->get_picture_path();

                    // Suppression de l'ancienne image et du thumbnail
                    if ($oldPath && file_exists($oldPath)) {
                        unlink($oldPath);
                        $oldThumb = str_replace('.jpg', '_thumbnail.jpg', $oldPath);
                        if (file_exists($oldThumb)) {
                            unlink($oldThumb);
                        }
                    }

                    // Dossier utilisateur
                    $userDir = "uploads/users/" . $member->get_id();
                    // si il n'y a pas de dossier, on le crée
                    if (!is_dir($userDir)) {
                        // Permission : owner peut r-w-x, group peut r-x, reste peut r-x
                        mkdir($userDir, 0755, true);
                    }

                    //pour avoir le nom originel du fichier (ne pas utiliser)
                    //$originalName = pathinfo($_FILES['image']['name'], PATHINFO_FILENAME);

                    $basePath = $userDir . "/profile";
                    // Image principale
                    $mainPath = $basePath . ".jpg";
                    // Thumbnail
                    $thumbPath = $basePath . "_thumbnail.jpg";

                    // Création images
                    Image::resizeToJpeg($_FILES['image']['tmp_name'], $mainPath, Configuration::get("IMAGE_MAX_WIDTH"), Configuration::get("IMAGE_MAX_HEIGHT"), Configuration::get("JPEG_QUALITY"));
                    Image::resizeToJpeg($_FILES['image']['tmp_name'], $thumbPath, Configuration::get("THUMB_MAX_WIDTH"), Configuration::get("THUMB_MAX_HEIGHT"), Configuration::get("JPEG_QUALITY"));

                    // Sauvegarde DB (image principale uniquement)
                    $member->set_picture_path($mainPath);
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
            $oldPath = $member->get_picture_path();
            if ($oldPath && file_exists($oldPath)) {
                unlink($oldPath);

                // Suppression du thumbnail associé
                $thumbPath = str_replace('.jpg', '_thumbnail.jpg', $oldPath);
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
            }
            $member->set_picture_path(null);
            $member->persist();

            $success = "Your profile picture has been successfully deleted.";
        }

        /**
         * LE RESTE EST COMMUN POUR LES 2 BOUTONS
         *
         */

        /**
         * REDIRECTION POST (PRG)
         */

        if (!empty($_POST) && empty($errors)) {
            $this->redirect("profile", "profile_picture", "ok");
        }

        /**
         * AFFICHAGE DU MESSAGE DE SUCCES (si on recharge la page en GET)
         */

        if (isset($_GET['param1']) && $_GET['param1'] === "ok") {
            $success = "Your profile has been successfully updated.";
        }

        (new View("profile_picture"))->show(["user" => $member, "errors" => $errors, "success" => $success]);
    }

    private function get_user_or_redirect_guess(bool $guest_pass = false): Member {
        /** @var Member $user */
        $user = $this->get_user_or_redirect();
        if ($user->is_guest() && !$guest_pass)
            $this->redirect("item", "browse");
        return $user;
    }
}
