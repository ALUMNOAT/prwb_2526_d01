<?php
require_once "framework/Model.php";
require_once "utils/Role.php";


class Member extends Model {

    public function __construct(
        private string $full_name,
        private string $email,
        private string $pseudo,
        private string $password,
        private Role $role,
        private ?int $id,
        private ?string $picture_path = null,
        private ?string $iban = null
    ) {
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
    public function get_id(): ?int {
        return $this->id;
    }
    public function get_picture_path(): ?string {
        return $this->picture_path;
    }
    public function get_iban(): ?string {
        return $this->iban;
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
    public function set_id(?int $new_id): void {
        $this->id = $new_id;
    }
    public function set_picture_path(?string $new_picture_path): void {
        $this->picture_path = $new_picture_path;
    }
    public function set_iban(?string $new_iban): void {
        $this->iban = $new_iban;
    }

    public function validate() : array {
        return [];
    }

    public function persist() : Member|array {
        if($this->id == NULL) {
            $errors = $this->validate();
            if(empty($errors)){
                self::execute(
                    'INSERT INTO users (full_name, email, pseudo, password, role, picture_path, iban) 
                    VALUES (:full_name, :email, :pseudo, :password, :role, :picture_path, :iban)', 
                    ['full_name' => $this->full_name,
                    'email' => $this->email,
                    'pseudo' => $this->pseudo,
                    'password' => $this->password,
                    'role' => $this->role->value,
                    'picture_path' => $this->picture_path ?? "",
                    'iban' => $this->iban ?? ""
                    ]
                );
                $member = self::get_member(self::lastInsertId());
                $this->id = $member->id;
                return $this;
            } else {
                return $errors; 
            }
        } else {
            //we need to add the eventual modifications
            throw new Exception("Not Implemented yet.");
        }
    }

    public function delete() : void {

    }

    public static function get_member(int $id) : ?Member {
        return new Member("test", "test@test.com", "testy", "Dummy123", Role::USER, 0);
    }
   
}