<?php
require_once "framework/Model.php";
require_once "model/Bid.php";
require_once "utils/Role.php";


class Member extends Model {

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
        private ?int $id,
        private string $full_name,
        private ?string $email,     //Luca: guests don't have emails
        private string $pseudo,
        private ?string $password,   //Luca: guests don't have passwords
        private Role $role,
        private ?string $picture_path = null,
        private ?string $iban = null,
    ) {
    }

    public function get_id(): ?int {
        return $this->id;
    }
    public function get_full_name(): string {
        return $this->full_name;
    }
    public function get_email(): ?string {
        return $this->email;
    }
    public function get_pseudo(): string {
        return $this->pseudo;
    }
    public function get_password(): ?string {
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

    public function persist(): Member {
        // Si l'ID existe (utilisateur existe), on update
        if ($this->id !== null) {
            // On met à jour l'utilisateur existant (un ID attribué déjà)
            self::execute(
                "UPDATE users 
                SET full_name=:full_name, email=:email, pseudo=:pseudo, password=:password, role=:role, picture_path=:picture_path, iban=:iban WHERE id=:id",
                ["id" => $this->id, "full_name" => $this->full_name, "email" => $this->email, "pseudo" => $this->pseudo, "password" => $this->password, "role" => $this->role->value, "picture_path" => $this->picture_path, "iban" => $this->iban]
            );
        } else {
            // Insertion nouveau utilisateur (pas d'ID)
            self::execute(
                "INSERT INTO users(full_name,email,pseudo,password,role,picture_path,iban) VALUES(:full_name, :email, :pseudo, :password, :role, :picture_path, :iban)",
                ["full_name" => $this->full_name, "email" => $this->email, "pseudo" => $this->pseudo, "password" => $this->password, "role" => $this->role->value, "picture_path" => $this->picture_path, "iban" => $this->iban]
            );
            // Récupère l'id auto-incrémenté généré par MySQL
            $this->id = self::lastInsertId();
        }

        return $this;
    }

    public static function get_member_by_pseudo(string $pseudo): Member|false {
        $query = self::execute(
            "SELECT * FROM users WHERE pseudo = :pseudo",
            [":pseudo" => $pseudo]
        );

        $data = $query->fetch();
        if ($query->rowCount() === 0)
            return false;
        else {
            // On transforme la string en enum
            $role = Role::from($data["role"]);
            return new Member($data["id"], $data["full_name"], $data["email"], $data["pseudo"], $data["password"], $role, $data["picture_path"], $data["iban"]);
        }
    }

    public static function get_member_by_email(string $email): Member|false {
        $query = self::execute(
            "SELECT * FROM users WHERE email = :email",
            [":email" => $email]
        );

        $data = $query->fetch();
        if ($query->rowCount() === 0)
            return false;
        else {
            // On transforme la string en enum
            $role = Role::from($data["role"]);
            return new Member($data["id"], $data["full_name"], $data["email"], $data["pseudo"], $data["password"], $role, $data["picture_path"], $data["iban"]);
        }
    }

    public static function get_member_by_full_name(string $full_name): Member|false {
        $query = self::execute(
            "SELECT * FROM users WHERE full_name = :full_name",
            [":full_name" => $full_name]
        );

        $data = $query->fetch();
        if ($query->rowCount() === 0)
            return false;
        else {
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

    private static function validate_password(string $password): array {
        $errors = [];

        // 8 charactères minimum
        if (strlen($password) < Configuration::get("password_min_length", 8)) {
            $errors[] = "Le mot de passe doit contenir au moins 8 charact&egrave;res.";
        }

        // Une majuscule, une minuscule, un chiffre, un charactère non alphanumérique
        if (!preg_match("/[A-Z]/", $password))
            $errors[] = "Le mot de passe doit contenir une majuscule";
        if (!preg_match("/[a-z]/", $password))
            $errors[] = "Le mot de passe doit contenir une minuscule";
        if (!preg_match("/\d/", $password))
            $errors[] = "Le mot de passe doit contenir un chiffre";
        if (!preg_match("/[^a-zA-Z0-9]/", $password))
            $errors[] = "Le mot de passe doit contenir un charactère non-alphanumérique.";

        return $errors;
    }

    public static function validate_passwords(string $password, string $password_confirm): array {
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

    public static function validate_unicity(string $pseudo, string $email, string $full_name): array {
        $errors = [];
        //TODO: do only one SQL query to verify unicity !
        //it's also better if it's self contained, ie the SQL query is inside this function
        $member_pseudo = self::get_member_by_pseudo($pseudo);
        $member_email = self::get_member_by_email($email);
        $member_full_name = self::get_member_by_full_name($full_name);
        if ($member_pseudo) {
            $errors[] = "Ce pseudo '$pseudo' existe déjà.";
        }
        if ($member_email) {
            $errors[] = "Cet émail '$email' existe déjà.";
        }
        if ($member_full_name) {
            $errors[] = "Ce nom d'utilisateur '$full_name' existe déjà.";
        }
        return $errors;
    }

    private static function check_password(string $clear_password, string $hash): bool {
        return password_verify($clear_password, $hash);
    }

    /**
     * Règles métiers Pseudo :
     *
     * [3;30] charactères
     */
    public function validate_pseudo(): array {
        $errors = [];

        if (strlen($this->pseudo) === 0)
            $errors[] = "Un pseudo est requis.";
        else if (!(strlen($this->pseudo) >= Configuration::get("pseudo_min_length", 3) && strlen($this->pseudo) <= Configuration::get("pseudo_max_length", 30)))
            $errors[] = "Le pseudo doit contenir entre 3 et 30 caractères.";
        /*
        else if (!strlen($this->pseudo) >= Configuration::get("pseudo_min_length", 3))
            $errors[] = "Le pseudo doit contenir au moins 3 caractères.";
        else if (!strlen($this->pseudo) <= Configuration::get("pseudo_max_length", 30))
            $errors[] = "Le pseudo doit contenir moins ou 30 caractères.";
            */
        return $errors;
    }

    /**
     * Règles métiers full_name :
     *
     * Minimum 3 charactères
     */
    public function validate_full_name(): array {
        $errors = [];

        if (strlen($this->full_name) === 0)
            $errors[] = "Un nom est requis.";
        else if (strlen($this->full_name) < Configuration::get("full_name_min_length", 3))
            $errors[] = "Le nom doit avoir au minimum 3 caractères.";
        else if (strlen($this->full_name) > Configuration::get("full_name_max_length", 255))
            $errors[] = "Le nom ne doit pas dépasser 255 charactère.";

        return $errors;
    }

    public function validate_email(): array {
        $errors = [];

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL))
            $errors[] = "L'adresse email n'est pas valide.";

        return $errors;
    }

    public static function validate_login(string $email, string $password): array {
        $errors = [];

        $member = self::get_member_by_email($email);
        if ($member) {
            if (!self::check_password($password, $member->password))
                $errors[] = "Le mot de passe est incorrect. Essayez encore une fois.";
        } else {
            $errors[] = "Un utilisateur avec cet email '$email' n'existe pas. Veuillez vous inscrire.";
        }

        return $errors;
    }

    public static function get_member(int $id): Member|false {
        $query = self::execute(
            "SELECT *
            FROM users 
            WHERE users.id = :user_id",
            [
                "user_id" => $id,
            ]
        );

        if ($query->rowCount() == 0)
            return false;

        $row = $query->fetch();
        $member = new Member(
            $row['id'],
            $row['full_name'],
            $row['email'],
            $row['pseudo'],
            $row['password'],
            Role::from($row['role']),
            $row['picture_path'],
            $row['iban']
        );

        return $member;
    }

    public function get_my_bidded_items(): array {
        /*
        doc: https://www.dataforgelabs.com/advanced-sql-concepts/join-with-subquery-sql
        doc: https://stackoverflow.com/questions/13131496/how-to-do-join-on-multiple-criteria-returning-all-combinations-of-both-criteria
        doc: https://stackoverflow.com/questions/1416980/how-to-fetch-result-from-mysql-row-with-multiple-same-name-columns-with-php
        doc: https://stackoverflow.com/questions/329931/sql-select-join-is-it-possible-to-prefix-all-columns-as-prefix
        doc: https://www.php.net/manual/en/function.array-column.php
        */

        //GUEST
        if ($this->id === null)
            return [];

        $query = self::execute(
            "SELECT *
            FROM v_items_status AS v  
            WHERE v.id IN (
                SELECT bids.item                
                FROM bids
                WHERE bids.owner = :user_id
            )
            AND v.end_at > :now
            ORDER BY v.end_at ASC",
            [
                "user_id" => $this->id,
                "now" => AppTime::get_current_datetime()
            ]
        );

        $data = $query->fetchAll();

        $owner = Member::get_members(array_column($data, 'owner'));
        $items = [];
        $my_bidded_items = [];

        foreach ($data as $row) {
            $item = new Item(
                $row['id'],
                $row['title'],
                $owner[$row['owner']],
                $row['created_at'],
                $row['duration_days'],
                [],
                $row['description'] ?? "",
                $row['buy_now_price'],
                $row['starting_bid']
            );

            $items[$row['id']] = $item;  //ça marche bien

            $my_bidded_items[$row['id']] = new ItemStatus(
                $item,
                $row['end_at'],
                $row['bid_count'],
                $row['max_bid'],
                $row['is_direct_sale'] === 1,
                $row['is_auction'] === 1,
                $row['has_buy_now'] === 1,
                $row['has_bids'] === 1,
                $row['buy_now_reached'] === 1,
                $row['not_purchased_direct_sale'] === 1
            );
        }

        $items_pictures = ItemPicture::get_items_picture($items);
        foreach ($items as $item) {
            $item->set_pictures($items_pictures[$item->get_id()]);
        }

        return $my_bidded_items;
    }

    public static function get_members(array $users_id): array {
        /*
        doc: https://stackoverflow.com/questions/907806/passing-an-array-to-a-query-using-a-where-clause
        doc: https://www.daniweb.com/programming/web-development/threads/266686/how-to-use-an-php-array-in-an-sql-query
        doc A: https://www.codestudy.net/blog/default-array-values-if-key-doesn-t-exist/#method-4-null-coalescing-assignment-
        */
        if (empty($users_id))
            return [];

        $placeholders = [];
        $params = [];
        foreach ($users_id as $i => $u) {
            $placeholders[] = ":u$i";
            $params[":u$i"] = $u;
        }

        $query = self::execute(
            "SELECT *
            FROM users  
            WHERE users.id IN ("
                . implode(',', $placeholders) .
                ")",
            $params
        );

        $data = $query->fetchAll();

        $users = [];
        foreach ($data as $row) {
            //see 'doc A' for '??='
            //it assigns a value if there are none already
            $users[$row['id']] ??= new Member(
                $row['id'],
                $row['full_name'],
                $row['email'],
                $row['pseudo'],
                $row['password'],
                Role::from($row['role']),
                $row['picture_path'],
                $row['iban']
            );
        }

        return $users;
    }

    public function get_my_bids(): array {
        /*
        doc: https://www.interviewquery.com/p/sql-max-case-when
        doc: https://sentry.io/answers/in-sql-how-do-i-select-only-rows-with-max-value-on-a-column/ --> see topchef's answer
        doc: https://stackoverflow.com/questions/38549/what-is-the-difference-between-inner-join-and-outer-join
        doc: https://stackoverflow.com/questions/15486131/select-distinct-and-max-in-the-same-query
        doc: https://stackoverflow.com/questions/4282413/sort-array-of-objects-by-one-property
        doc: https://stackoverflow.com/questions/45420576/syntax-error-column-isnt-in-group-by-sql-laravel
        doc: https://stackoverflow.com/questions/41571271/group-by-not-working-laravel
        doc: https://dev.mysql.com/doc/refman/5.7/en/group-by-handling.html
        */
        $query = self::execute(
            "SELECT b.owner, b.item, b.created_at, b.amount, global_max
            FROM (
                SELECT bids.owner, bids.item, bids.created_at, bids.amount,
                ROW_NUMBER() OVER (PARTITION BY bids.item ORDER BY bids.amount DESC) ranked_order
                FROM bids
                WHERE bids.owner = :user_id
            ) AS b
            LEFT JOIN (
                SELECT bids.item, MAX(bids.amount) AS global_max
                FROM bids
                GROUP BY bids.item
            ) AS max_bids
                ON (max_bids.item) = (b.item)
            JOIN v_items_status AS v    
                ON v.id = b.item
            WHERE b.ranked_order = 1
            ORDER BY v.end_at ASC;",   
            ["user_id" => $this->id]
        );

        $my_bids = [];

        if($query->rowCount() > 0) {
            $data = $query->fetchAll();

            $items_id = array_column($data, 'item');
            $items = Item::get_items($items_id);

            $items_status = ItemStatus::get_items_status($items);

            $owners_id = array_column($data, 'owner');
            $owners = Member::get_members($owners_id);

            foreach($data as $row) {
                $is_highest_bid = $row['amount'] == $row['global_max'];
                $owner = $owners[$row['owner']];
                $item_status = $items_status[$row['item']];

                $my_bids[$row['item']] = new Bid(
                    $owner,
                    $item_status,
                    $row['created_at'],
                    $row['amount'],
                    $is_highest_bid
                );
            }

            /*
            //If needs be to sort in PHP, I could use usort with an anonymous function :)
            usort($my_bids, function ($a, $b) {
                return 
            })
                */
        }

        return $my_bids;
    }
}
