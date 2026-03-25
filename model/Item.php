<?php
require_once "framework/Model.php";
require_once "utils/AppTime.php";
require_once "model/Member.php";
require_once "model/ItemPicture.php";
require_once "model/ItemPicturesCollection.php";




class Item extends Model {

    public function __construct(
        private ?int $id, // id nullable pour la création d'item
        private string $title,
        private ?string $description,
        private Member $owner,
        private string $created_at, //this needs to be its own class in utils ? would make it easier, string for now
        private ?float $buy_now_price,
        private int $duration_days,
        private ?float $starting_bid,
        private ?ItemStatus $status = null,
        private ?ItemPicturesCollection $pictures_ = null,
        private ?ItemBidsCollection $bids = null
    ) {
    }

    public function get_id(): ?int {
        return $this->id;
    }
    public function get_title(): string {
        return $this->title;
    }
    public function get_description(): ?string {
        return $this->description;
    }
    public function get_owner(): Member {
        return $this->owner;
    }
    public function get_created_at(): string { // /!\
        return $this->created_at;
    }
    public function get_buy_now_price(): ?float {
        return $this->buy_now_price;
    }
    public function get_duration_days(): int {
        return $this->duration_days;
    }
    public function get_starting_bid(): ?float {
        return $this->starting_bid;
    }
    public function get_status(): ItemStatus {
        return $this->status;
    }
    public function get_pictures_(): ?ItemPicturesCollection {
        return $this->pictures_;
    }
    public function get_bids(): ItemBidsCollection {
        return $this->bids;
    }

    public function set_id(int $new_id): void {
        $this->id = $new_id;
    }
    public function set_title(string $new_title): void {
        $this->title = $new_title;
    }
    public function set_description(?string $new_description): void {
        $this->description = $new_description;
    }
    public function set_owner(Member $new_owner): void {
        $this->owner = $new_owner;
    }
    public function set_created_at(string $new_created_at): void { // /!\
        $this->created_at = $new_created_at;
    }
    public function set_buy_now_price(?float $new_buy_now_price): void {
        $this->buy_now_price = $new_buy_now_price;
    }
    public function set_duration_days(int $new_duration_day): void {
        $this->duration_days = $new_duration_day;
    }
    public function set_starting_bid(?float $new_starting_bid): void {
        $this->starting_bid = $new_starting_bid;
    }
    public function set_status(ItemStatus $status): void {
        $this->status = $status;
    }
    public function set_pictures_(ItemPicturesCollection $pictures_): void {
        $this->pictures_ = $pictures_;
    }
    public function set_bids(ItemBidsCollection $bids): void {
        $this->bids = $bids;
    }


    public function is_owner(Member $user): bool {
        return $this->owner->get_id() == $user->get_id();
    }

    public static function get_item(int $id): Item|false {
        /*
        doc 'A' : https://www.reddit.com/r/SQL/comments/10ytngc/how_do_i_join_two_tables_with_all_fields_from/
        doc 'B' : https://stackoverflow.com/questions/1921421/get-the-first-element-of-an-array
        doc 'C' : https://www.php.net/manual/en/backedenum.from.php
        */
        $query = self::execute(
            "SELECT v.id AS item_id, v.title, v.description, v.created_at, v.buy_now_price, v.duration_days, v.starting_bid, 
            v.end_at, v.bid_count, v.max_bid, v.is_direct_sale, v.is_auction, 
            v.has_buy_now, v.has_bids, v.buy_now_reached, v.not_purchased_direct_sale,
            users.id AS user_id, users.full_name, users.email, users.pseudo, users.password, users.role, users.picture_path AS user_picture, users.iban,
                FROM v_items_status AS v
                JOIN users ON v.owner = users.id
                WHERE v.id = :id",
            ["id" => $id]
        );

        if ($query->rowCount() == 0)
            return false;
        else {
            $row = $query->fetch();
            //OWNER
            $owner = new Member(
                $row['user_id'],
                $row['full_name'],
                $row['email'],
                $row['pseudo'],
                $row['password'],
                Role::from($row['role']),
                $row['user_picture'],
                $row['iban']
            );
            //ITEM
            $item = new Item(
                $row['item_id'],
                $row['title'],
                $row['description'] ?? "",
                $owner,
                $row['created_at'],
                $row['buy_now_price'],
                $row['duration_days'],
                $row['starting_bid'],
            );

            $status = new ItemStatus(
                $item,
                $row['end_at'],
                $row['bid_count'],
                $row['max_bid'] ?? null,
                $row['is_direct_sale'] == 1,
                $row['is_auction'] == 1,
                $row['has_buy_now'] == 1,
                $row['has_bids'] == 1,
                $row['buy_now_reached'] == 1,
                $row['not_purchased_direct_sale'] == 1,
            );
            $item->set_status($status);

            $item->set_pictures_(ItemPicturesCollection::get_item_pictures_from_db($item));

            return $item;
        }
    }

    public function delete(): void {
        /*
        doc: https://www.w3schools.com/mysql/mysql_delete.asp
        doc: https://dba.stackexchange.com/questions/232987/deleting-multiple-tables-in-a-single-delete-query-in-mysql
        doc: https://stackoverflow.com/questions/31136434/mysql-deleting-rows-based-on-a-condition-with-data-from-another-table-and-no-jo
        */
        $query = self::execute(
            "DELETE FROM item_pictures AS p WHERE p.item = :item_id;
            DELETE FROM items WHERE items.id = :item_id;
            DELETE FROM v_items_status AS v WHERE v.id = :item_id;",
            ['item_id' => $this->id]
        );
    }

    public static function get_items(array $ids): array {
        $items = [];

        if (!empty($ids)) {
            $placeholders = [];
            $params = [];
            foreach ($ids as $id) {
                $placeholders[] = ":u$id";
                $params[":u$id"] = $id;
            }

            $query = self::execute(
                "SELECT *
                FROM items
                WHERE items.id IN ("
                    . implode(',', $placeholders) .
                    ")",
                $params
            );

            $data = $query->fetchAll();
            $owners_id = array_column($data, 'owner');
            $owners = Member::get_members($owners_id);

            foreach ($data as $row) {
                $items[$row['id']] = new Item(
                    $row['id'],
                    $row['title'],
                    $row['description'] ?? "",
                    $owners[$row['owner']],
                    $row['created_at'],
                    $row['buy_now_price'],
                    $row['duration_days'],
                    $row['starting_bid'],
                );
            }

            $items_pictures = ItemPicturesCollection::get_items_pictures_from_db($items);
            foreach ($items as $id => $item) {
                $item->set_pictures_($items_pictures[$id]);
            }

        }

        return $items;
    }

    public static function get_all_available_items(Member $user): array {
        $query = self::execute(
            "SELECT *
            FROM v_items_status AS v
            WHERE v.end_at > :now
            AND v.buy_now_reached = 0
            AND v.owner != :user_id
            ORDER BY v.end_at ASC",
            [
                "now" => AppTime::get_current_datetime(),
                "user_id" => $user->is_guest()
                    ? 0
                    : $user->get_id()
            ]
        );

        $data = $query->fetchAll();

        $owner = Member::get_members(array_column($data, 'owner'));
        $items = [];
        $available_items = [];

        foreach ($data as $row) {
            $item = new Item(
                $row['id'],
                $row['title'],
                $row['description'] ?? "",
                $owner[$row['owner']],
                $row['created_at'],
                $row['buy_now_price'],
                $row['duration_days'],
                $row['starting_bid'],
            );

            $items[$row['id']] = $item;

            $available_items[$row['id']] = new ItemStatus(
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

        $items_pictures = ItemPicturesCollection::get_items_pictures_from_db($items);
        foreach ($items as $id => $item) {
            $item->set_pictures_($items_pictures[$id]);
        }

        return $available_items;
    }

    public function validate_title(): array {
        $errors = [];

        if (
            mb_strlen($this->title) < Configuration::get("title_min_length", 3)
            || mb_strlen($this->title) > Configuration::get("title_max_length", 255)
        ) {
            $errors[] = "Title length must be between 3 and 255 characters";
        }

        return $errors;
    }

    public function validate_ipp(): array {
        $errors = [];

        if ($this->buy_now_price !== null && $this->starting_bid !== null) {
            if ($this->buy_now_price < $this->starting_bid) {
                $errors[] = "Buy now price must be greater than the starting bid.";
            }
            if ($this->buy_now_price < 0) {
                $errors[] = "Buy now price must be greater than 0.";
            }
        }

        return $errors;
    }

    public function validate_bid(): array {
        $errors = [];

        if ($this->starting_bid !== null && $this->starting_bid < 0) {
            $errors[] = "Starting bid must be positive.";
        }

        return $errors;
    }

    public function persist(): Item {
        if ($this->id !== null) {
            // Mise à jour d'un item existant
            self::execute(
                "UPDATE items
             SET title = :title,
                 description = :description,
                 buy_now_price = :buy_now_price,
                 duration_days = :duration_days,
                 starting_bid = :starting_bid
             WHERE id = :id",
                [
                    "id" => $this->id,
                    "title" => $this->title,
                    "description" => $this->description,
                    "buy_now_price" => $this->buy_now_price,
                    "duration_days" => $this->duration_days,
                    "starting_bid" => $this->starting_bid
                ]
            );
        } else {
            // Création d'un nouvel item
            $now = AppTime::get_current_datetime(); // date de création

            self::execute(
                "INSERT INTO items(title, description, owner, created_at, buy_now_price, duration_days, starting_bid)
             VALUES(:title, :description, :owner, :created_at, :buy_now_price, :duration_days, :starting_bid)",
                [
                    "title" => $this->title,
                    "description" => $this->description,
                    "owner" => $this->owner->get_id(),
                    "created_at" => $now,
                    "buy_now_price" => $this->buy_now_price,
                    "duration_days" => $this->duration_days,
                    "starting_bid" => $this->starting_bid
                ]
            );

            // Synchronisation de l'objet avec la base
            $this->id = self::lastInsertId();
            $this->created_at = $now;
        }

        return $this;
    }

    public function unicity(): array {
        $errors = [];

        if ($this->get_id() === null) {
            // CAS ADD
            $query = self::execute(
                "SELECT id FROM items
             WHERE title = :title
             AND owner = :owner",
                [
                    "title" => $this->get_title(),
                    "owner" => $this->owner->get_id()
                ]
            );
        } else {
            // CAS EDIT
            $query = self::execute(
                "SELECT id FROM items
             WHERE title = :title
             AND owner = :owner
             AND id != :id",
                [
                    "title" => $this->get_title(),
                    "owner" => $this->owner->get_id(),
                    "id"    => $this->get_id()
                ]
            );
        }

        if ($query->fetch()) {
            $errors[] = "Item already exists.";
        }

        return $errors;
    }

    public function equal($other_item): bool {
        return $this->id === $$other_item->get_id();
    }
}
