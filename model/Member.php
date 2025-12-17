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
        private ?string $iban = null,
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

    public function validate(): array {
        return [];
    }

    public function persist(): Member|array {
        if ($this->id == NULL) {
            $errors = $this->validate();
            if (empty($errors)) {
                self::execute(
                    'INSERT INTO users (full_name, email, pseudo, password, role, picture_path, iban) 
                    VALUES (:full_name, :email, :pseudo, :password, :role, :picture_path, :iban)',
                    [
                        'full_name' => $this->full_name,
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

    public function delete(): void {
    }

    public static function get_member(int $id): ?Member {
        return new Member("test", "test@test.com", "testy", "Dummy123", Role::USER, 0);
    }

    private function get_my_bidded_items(): array {
        /*
        doc : https://www.dataforgelabs.com/advanced-sql-concepts/join-with-subquery-sql
        doc: https://stackoverflow.com/questions/13131496/how-to-do-join-on-multiple-criteria-returning-all-combinations-of-both-criteria
        doc: https://stackoverflow.com/questions/1416980/how-to-fetch-result-from-mysql-row-with-multiple-same-name-columns-with-php
        doc: https://stackoverflow.com/questions/329931/sql-select-join-is-it-possible-to-prefix-all-columns-as-prefix
        doc: https://www.php.net/manual/en/function.array-column.php
        */
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
                "user_id" => $this->get_id(),
                "now" => AppTime::get_current_datetime()
            ]
        );

        $data = $query->fetchAll();

        $owner = Member::get_members(array_column($data, 'id'));
        $item_pictures = ItemPicture::get_items_pictures(array_column($data, 'item'));
        $my_bidded_items = [];

        foreach ($data as $row) {
            $item = new Item(
                $row['id'],
                $row['title'],
                $owner[$row['id']],
                $row['created_at'],
                $row['duration_days'],
                $item_pictures[$row['id']],
                $row['description'] ?? "",
                $row['buy_now_price'],
                $row['starting_bid']
            );

            $my_bidded_items[] = new ItemStatus(
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

        return $my_bidded_items;
    }

    private function get_users(array $users_id): array {
        $query = self::execute(
            "SELECT *
            FROM users  
            WHERE users.id IN (
                :users_id
            )
            AND v.end_at > :now
            ORDER BY v.end_at ASC",
            [
                "users_id" => implode(',', $users_id)
            ]
        );

        $data = $query->fetchAll();
        $users = [];
        foreach ($data as $row) {
            $users[$row['id']] = new Member(
                $row['full_name'],
                $row['email'],
                $row['pseudo'],
                $row['password'],
                Role::from($row['role']),
                $row['id'],
                $row['picture_path'],
                $row['iban']
            );
        }

        return $users;
    }
}
