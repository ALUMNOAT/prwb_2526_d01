<?php
require_once "framework/Model.php";
require_once "utils/Role.php";


class Member extends Model {

    // ID s'autoincrémente, ce qui veut dire que pas besoin de le mettre quand on créé un membre
    // Du coup je l'ai mis derrière vu qu'il est optionel (flemme d'ecrire des "null" pour creer un membre)
    public function __construct(
        private string $full_name,
        private string $email,
        private string $pseudo,
        private string $password,
        private Role $role,
        private ?string $picture_path = null,
        private ?int $id = null,
        private ?string $iban = null
    )
    { }

    public function get_id(): ?int {
        return $this->id;
    }
    public function get_full_name(): string {
        return $this->full_name;
    }
    public function get_email(): string {
        return $this->email;
    }
    public function get_pseudo(): string {
        return $this->pseudo;
    }
    public function get_password(): string {
        return $this->password;
    }
    public function get_role(): Role {
        return $this->role;
    }
    public function get_picture_path(): ?string {
        return $this->picture_path;
    }
    public function get_iban(): ?string {
        return $this->iban;
    }

    public function set_id(int $new_id): void {
        $this->id = $new_id;
    }
    public function set_full_name(string $new_full_name): void {
        $this->full_name = $new_full_name;
    }
    public function set_email(string $new_email): void {
        $this->email = $new_email;
    }
    public function set_pseudo(string $new_pseudo): void {
        $this->pseudo = $new_pseudo;
    }
    public function set_password(string $new_password): void {
        $this->password = $new_password;
    }
    public function set_role(Role $new_role): void {
        $this->role = $new_role;
    }
    public function set_picture_path(string $new_picture_path): void {
        $this->picture_path = $new_picture_path;
    }
    public function set_iban(string $new_iban): void {
        $this->iban = $new_iban;
    }

    public function persist(): Member {
        if(self::get_member_by_pseudo($this->pseudo)) {
            self::execute("UPDATE users SET full_name=:full_name, email=:email, password=:password, role=:role, picture_path=:picture_path, iban=:iban WHERE pseudo=:pseudo",
                ["full_name" => $this->full_name, "email" => $this->email, "pseudo" => $this->pseudo, "password" => $this->password, "role" => $this->role->value, "picture_path" => $this->picture_path, "iban" => $this->iban]);
        } else {
            self::execute("INSERT INTO users(full_name,email,pseudo,password,role,picture_path,iban) VALUES(:full_name, :email, :pseudo, :password, :role, :picture_path, :iban)",
                ["full_name" => $this->full_name, "email" => $this->email, "pseudo" => $this->pseudo, "password" => $this->password, "role" => $this->role->value, "picture_path" => $this->picture_path, "iban" => $this->iban]);
            // récupère l'id auto-incrémenté généré par MySQL
            $this->id = self::lastInsertId();
        }
        return $this;
    }

    public static function get_member_by_pseudo(string $pseudo): Member|false {
        $query = self::execute("SELECT * FROM users WHERE pseudo = :pseudo", [":pseudo" => $pseudo]);
        $data = $query->fetch();
        if ($query->rowCount() === 0) {
            return false;
        } else {
            // On transforme la string en enum
            $role = Role::from($data["role"]);
            return new Member($data["full_name"], $data["email"], $data["pseudo"], $data["password"], $role, $data["picture_path"], $data["id"], $data["iban"]);
        }
    }

    public static function get_member_by_email(string $email): Member|false {
        $query = self::execute("SELECT * FROM users WHERE email = :email", [":email" => $email]);
        $data = $query->fetch();
        if ($query->rowCount() === 0) {
            return false;
        } else {
            // On transforme la string en enum
            $role = Role::from($data["role"]);
            return new Member($data["full_name"], $data["email"], $data["pseudo"], $data["password"], $role, $data["picture_path"], $data["id"], $data["iban"]);
        }
    }

    private static function validate_password(string $password): array {
        $errors = [];

        // 8 charactères minimum
        if (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 charact&egrave;res.";
        }

        // Une majuscule, une minuscule, un chiffre, un charactère non alphanumérique
        if (!((preg_match("/[A-Z]/", $password)) && preg_match("/[a-z]/", $password) && preg_match("/\d/", $password) && preg_match("/[^a-zA-Z0-9]/", $password))) {
            $errors[] = "Le mot de passe doit contenir une majuscule, une minuscule, un chiifre et un charact&egrave;re non alphanum&eacute;rique.";
        }
        return $errors;
    }

    public static function validate_passwords(string $password, string $password_confirm) : array {
        $errors = Member::validate_password($password);
        if ($password !== $password_confirm) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
        return $errors;
    }

    public static function validate_unicity(string $pseudo, string $email) : array {
        $errors = [];
        $member_pseudo = self::get_member_by_pseudo($pseudo);
        $member_email = self::get_member_by_email($email);
        if ($member_pseudo) {
            $errors[] = "Cet nom d'utilisateur '$pseudo' existe d&eacute;j&agrave;.";
        }
        if ($member_email) {
            $errors[] = "Cet email '$email' existe d&eacute;j&agrave;.";
        }
        return $errors;
    }

    private static function check_password(string $clear_password, string $hash) : bool {
        return password_verify($clear_password, $hash);
    }

    public function validate_pseudo() : array {
        $errors = [];
        if (strlen($this->pseudo) === 0) {
            $errors[] = "Un pseudo est requis.";
        }
        if (!(strlen($this->pseudo) >= 3 && strlen($this->pseudo) <= 30 )) {
            $errors[] = "Le pseudo doit contenir entre 3 et 30 caract&egrave;res.";
        }
        return $errors;
    }

    public static function validate_login(string $email, string $password) : array {
        $errors = [];
        $member = self::get_member_by_email($email);
        if ($member) {
            if (!self::check_password($password, $member->password)) {
                $errors[] = "Le mot de passe est incorrect. Essayez encore une fois.";
            }
        }
        else {
            $errors[] = "Un utilisateur avec cet email '$email' n'existe pas. Veuillez vous inscrire.";
        }
        return $errors;
    }

}