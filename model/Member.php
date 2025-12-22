<?php
require_once "framework/Model.php";
require_once "utils/Role.php";


class Member extends Model
{

    /**
     * ID nullable :
     *
     * Un nouveau utilisateur n'a pas d'ID (la base de données lui en donne un).
     *
     * Un utilisateur existant a déjà un ID.
     *
     * On fait comme ça pour ne pas avoir d'insertions vides quand on inscrit quelqu’un.
     */

    public function __construct(
        private ?int    $id,
        private string  $full_name,
        private string  $email,
        private string  $pseudo,
        private string  $password,
        private Role    $role,
        private ?string $picture_path = null,
        private ?string $iban = null
    )
    {
    }

    public function get_id(): int
    {
        return $this->id;
    }

    public function get_full_name(): string
    {
        return $this->full_name;
    }

    public function get_email(): string
    {
        return $this->email;
    }

    public function get_pseudo(): string
    {
        return $this->pseudo;
    }

    public function get_password(): string
    {
        return $this->password;
    }

    public function get_role(): Role
    {
        return $this->role;
    }

    public function get_picture_path(): ?string
    {
        return $this->picture_path;
    }

    public function get_iban(): ?string
    {
        return $this->iban;
    }

    public function set_id(int $new_id): void
    {
        $this->id = $new_id;
    }

    public function set_full_name(string $new_full_name): void
    {
        $this->full_name = $new_full_name;
    }

    public function set_email(string $new_email): void
    {
        $this->email = $new_email;
    }

    public function set_pseudo(string $new_pseudo): void
    {
        $this->pseudo = $new_pseudo;
    }

    public function set_password(string $new_password): void
    {
        $this->password = $new_password;
    }

    public function set_role(Role $new_role): void
    {
        $this->role = $new_role;
    }

    public function set_picture_path(?string $new_picture_path): void
    {
        $this->picture_path = $new_picture_path;
    }

    public function set_iban(?string $new_iban): void
    {
        $this->iban = $new_iban;
    }

    public function persist(): Member
    {
        // Si l'ID existe (utilisateur existe), on update
        if ($this->id !== null) {
            // On met à jour l'utilisateur existant (un ID attribué déjà)
            self::execute("UPDATE users SET full_name=:full_name, email=:email, pseudo=:pseudo, password=:password, role=:role, picture_path=:picture_path, iban=:iban WHERE id=:id",
                ["id" => $this->id, "full_name" => $this->full_name, "email" => $this->email, "pseudo" => $this->pseudo, "password" => $this->password, "role" => $this->role->value, "picture_path" => $this->picture_path, "iban" => $this->iban]);
        } else {
            // Insertion nouveau utilisateur (pas d'ID)
            self::execute("INSERT INTO users(full_name,email,pseudo,password,role,picture_path,iban) VALUES(:full_name, :email, :pseudo, :password, :role, :picture_path, :iban)",
                ["full_name" => $this->full_name, "email" => $this->email, "pseudo" => $this->pseudo, "password" => $this->password, "role" => $this->role->value, "picture_path" => $this->picture_path, "iban" => $this->iban]);
            // Récupère l'id auto-incrémenté généré par MySQL
            $this->id = self::lastInsertId();
        }
        return $this;
    }

    public static function get_member_by_pseudo(string $pseudo): Member|false
    {
        $query = self::execute("SELECT * FROM users WHERE pseudo = :pseudo", [":pseudo" => $pseudo]);
        $data = $query->fetch();
        if ($query->rowCount() === 0) {
            return false;
        } else {
            // On transforme la string en enum
            $role = Role::from($data["role"]);
            return new Member($data["id"], $data["full_name"], $data["email"], $data["pseudo"], $data["password"], $role, $data["picture_path"], $data["iban"]);
        }
    }

    public static function get_member_by_email(string $email): Member|false
    {
        $query = self::execute("SELECT * FROM users WHERE email = :email", [":email" => $email]);
        $data = $query->fetch();
        if ($query->rowCount() === 0) {
            return false;
        } else {
            // On transforme la string en enum
            $role = Role::from($data["role"]);
            return new Member($data["id"], $data["full_name"], $data["email"], $data["pseudo"], $data["password"], $role, $data["picture_path"], $data["iban"]);
        }
    }

    public static function get_member_by_full_name(string $full_name): Member|false
    {
        $query = self::execute("SELECT * FROM users WHERE full_name = :full_name", [":full_name" => $full_name]);
        $data = $query->fetch();
        if ($query->rowCount() === 0) {
            return false;
        } else {
            // On transforme la string en enum
            $role = Role::from($data["role"]);
            return new Member($data["id"], $data["full_name"], $data["email"], $data["pseudo"], $data["password"], $role, $data["picture_path"], $data["iban"]);
        }
    }

    /**
     * Règles metiers du Password :
     *
     * Minimum 8 charactères
     *
     * Doit contenir un chiffre, une Maj, une min, un symbole
     */

    private static function validate_password(string $password): array
    {
        $errors = [];

        // 8 charactères minimum
        if (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 charact&egrave;res.";
        }

        // Une majuscule, une minuscule, un chiffre, un charactère non alphanumérique
        if (!((preg_match("/[A-Z]/", $password)) && preg_match("/[a-z]/", $password) && preg_match("/\d/", $password) && preg_match("/[^a-zA-Z0-9]/", $password))) {
            $errors[] = "Le mot de passe doit contenir une majuscule, une minuscule, un chiffre et un charact&egrave;re non alphanum&eacute;rique.";
        }
        return $errors;
    }

    public static function validate_passwords(string $password, string $password_confirm): array
    {
        $errors = Member::validate_password($password);
        if ($password !== $password_confirm) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
        return $errors;
    }

    /**
     * Règles metiers de l'unique :
     *
     * email et pseudo doivent etre unique
     */

    public static function validate_unicity(string $pseudo, string $email, string $full_name, ?int $exclude_id = null): array
    {
        $errors = [];
        $member_pseudo = self::get_member_by_pseudo($pseudo);
        $member_email = self::get_member_by_email($email);
        $member_full_name = self::get_member_by_full_name($full_name);
        if ($member_pseudo && $member_pseudo->get_id() !== $exclude_id) {
            $errors[] = "Ce pseudo '$pseudo' existe d&eacute;j&agrave;.";
        }
        if ($member_email && $member_email->get_id() !== $exclude_id) {
            $errors[] = "Cet email '$email' existe d&eacute;j&agrave;.";
        }
        if ($member_full_name && $member_full_name->get_id() !== $exclude_id) {
            $errors[] = "Ce nom d'utilisateur '$full_name' existe d&eacute;j&agrave;.";
        }
        return $errors;
    }

    private static function check_password(string $clear_password, string $hash): bool
    {
        return password_verify($clear_password, $hash);
    }

    /**
     * Règles métiers Pseudo :
     *
     * [3;30] charactères
     */

    public function validate_pseudo(): array
    {
        $errors = [];
        if (strlen($this->pseudo) === 0) {
            $errors[] = "Un pseudo est requis.";
        }
        if (!(strlen($this->pseudo) >= 3 && strlen($this->pseudo) <= 30)) {
            $errors[] = "Le pseudo doit contenir entre 3 et 30 caract&egrave;res.";
        }
        return $errors;
    }

    /**
     * Règles métiers full_name :
     *
     * Minimum 3 charactères
     */

    public function validate_full_name(): array
    {
        $errors = [];
        if (strlen($this->full_name) === 0) {
            $errors[] = "Un nom est requis.";
        }
        if (!(strlen($this->full_name) > 3)) {
            $errors[] = "Le nom doit avoir minimum 3 caract&egrave;res.";
        }
        return $errors;
    }

    public function validate_email(): array
    {
        $errors = [];
        if (!(filter_var($this->email, FILTER_VALIDATE_EMAIL))) {
            $errors[] = "L'adresse email n'est pas valide.";
        }
        return $errors;
    }

    public static function validate_login(string $email, string $password): array
    {
        $errors = [];
        $member = self::get_member_by_email($email);
        if ($member) {
            if (!self::check_password($password, $member->password)) {
                $errors[] = "Le mot de passe est incorrect. Essayez encore une fois.";
            }
        } else {
            $errors[] = "Un utilisateur avec cet email '$email' n'existe pas. Veuillez vous inscrire.";
        }
        return $errors;
    }

    /**
     * An IBAN is validated by converting it into an integer and performing a basic mod-97 operation (as described in ISO 7064) on it.
     * If the IBAN is valid, the remainder equals 1. The algorithm of IBAN validation is as follows:
     *
     * 1) Check that the total IBAN length is correct as per the country. If not, the IBAN is invalid
     *
     * 2) Move the four initial characters to the end of the string
     *
     * 3) Replace each letter in the string with two digits, thereby expanding the string, where A = 10, B = 11, ..., Z = 35
     *
     * 4) Interpret the string as a decimal integer and compute the remainder of that number on division by 97
     *
     * If the remainder is 1, the check digit test is passed and the IBAN might be valid.
     *
     * Example (fictitious United Kingdom bank, sort code 12-34-56, account number 98765432):
     *
     * • IBAN:        GB82 WEST 1234 5698 7654 32
     *
     * • Rearrange:        WEST 1234 5698 7654 32 GB82
     *
     * • Convert to integer:        3214282912345698765432161182
     *
     * • Compute remainder:        3214282912345698765432161182    mod 97 = 1
     *
     */
    public static function validate_iban(string $iban): array
    {
        $errors = [];
        // Pour ne pas avoir 3 retours dans la fonction, on va créer un booléen
        $isvalid = true;

        // Vérification IBAN belge
        if (!preg_match('/^BE\d{2}(?: ?\d{4}){3}$/', $iban)) {
            $errors[] = "L'IBAN '$iban' n'est pas un IBAN Belge.";
            $isvalid = false;
        }

        // Si regex non valide, on renvoit une erreur, sinon on continue et on renvoie une erreur aussi
        // Une seule erreur partout
        if ($isvalid) {
            // Nettoyer l'IBAN (je fait pas le check ou je mets tout en uppercase car le regex le fait)
            $iban = str_replace(' ', '', $iban); // enlève tous les espaces


            $small_sublist_iban = substr($iban, 0, 4); //BE99 [0;3]
            $big_sublist_iban = substr($iban, 4, strlen($iban)); // le reste

            $converted = '';
            for ($i = 0; $i < strlen($small_sublist_iban) - 2; $i++) {
                // char(B) = 66, 66-55 = 11, B = 11 ici
                // le code peut bug mais pas possible pratiquement car regex check
                $converted .= ord($small_sublist_iban[$i]) - 55;
            }

            // On rajoute les 2 nombres après BE  (99 par exemple si BE99)
            $converted .= substr($small_sublist_iban, 2, 2);

            $verify_iban = $big_sublist_iban . $converted;

            if (bcmod($verify_iban, '97') !== '1') {
                $errors[] = "L'IBAN '$iban' n'est pas valide.";
            }
        }
        return $errors;
    }

    public static function validate_photo(array $file): array
    {
        $errors = [];

        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Une erreur est survenue lors de l'upload.";
            return $errors;
        }

        if ($file['size'] > Configuration::get("MAX_UPLOAD_SIZE")) {
            $errors[] = "Le fichier dépasse la taille maximale autorisée (5MB).";
        }

        $mime = mime_content_type($file['tmp_name']);
        $valid_types = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp'
        ];

        // Si j'envoye un fichier .mp3, ceci s'affiche
        if (!in_array($mime, $valid_types, true)) {
            $errors[] = "Format d'image non supporté.";
        }

        return $errors;
    }

}