<?php
require_once "framework/Model.php";
require_once "utils/AppTime.php";
require_once "model/Member.php";
require_once "model/ItemPicture.php";
require_once "model/Category.php";




class Item extends Model implements JsonSerializable {

    public function __construct(
        private ?int $id, // id nullable pour la création d'item
        private string $title,
        private Member $owner,
        private string $created_at, //this needs to be its own class in utils ? would make it easier, string for now
        private int $duration_days,
        private array $pictures = [],
        private array $categories = [],
        private ?string $description = null,
        private ?float $buy_now_price = null,
        private ?float $starting_bid = null,
    ) {
    }

    public function jsonSerialize() : array {
        //https://www.slingacademy.com/article/how-to-convert-an-object-to-json-in-php-serialization/
        return [
            "id" => $this->id,
            "title" => $this->title,
            "owner" => $this->owner,
            "created_at" => $this->created_at,
            "duration_days" => $this->duration_days,
            "pictures" => $this->pictures,
            "categories" => $this->categories,
            "description" => $this->description,
            "buy_now_price" => $this->buy_now_price,
            "starting_bid" => $this->starting_bid,
        ];
    }

    public function get_id(): ?int {
        return $this->id;
    }
    public function get_title(): string {
        return $this->title;
    }
    public function get_owner(): Member {
        return $this->owner;
    }
    public function get_created_at(): string { // /!\
        return $this->created_at;
    }
    public function get_duration_days(): int {
        return $this->duration_days;
    }
    public function get_pictures(): array {
        return $this->pictures;
    }
    public function get_categories(string $order = 'alphabetic'): array {
        //https://www.php.net/manual/en/function.asort.php
        switch($order) {
            case 'alphabetic' :
                asort($this->categories);
        }

        return $this->categories;
        
    }
    public function get_description(): ?string {
        return $this->description;
    }
    public function get_buy_now_price(): ?float {
        return $this->buy_now_price;
    }
    public function get_starting_bid(): ?float {
        return $this->starting_bid;
    }

    public function set_id(int $new_id): void {
        $this->id = $new_id;
    }
    public function set_title(string $new_title): void {
        $this->title = $new_title;
    }
    public function set_owner(Member $new_owner): void {
        $this->owner = $new_owner;
    }
    public function set_created_at(string $new_created_at): void { // /!\
        $this->created_at = $new_created_at;
    }
    public function set_duration_days(int $new_duration_day): void {
        $this->duration_days = $new_duration_day;
    }
    public function set_pictures(array $pictures): void {
        $this->pictures = $pictures;
    }
    public function set_categories(array $categories): void {
        $this->categories = $categories;
    }
    public function set_description(?string $new_description): void {
        $this->description = $new_description;
    }
    public function set_buy_now_price(?float $new_buy_now_price): void {
        $this->buy_now_price = $new_buy_now_price;
    }
    public function set_starting_bid(?float $new_starting_bid): void {
        $this->starting_bid = $new_starting_bid;
    }

    public function get_picture_count(): int {
        return count($this->pictures);
    }
    public function has_single_picture(): bool {
        return $this->get_picture_count() === 1;
    }
    public function has_no_picture(): bool {
        return $this->get_picture_count() === 0;
    }
    public function has_many_pictures(): bool {
        return count($this->pictures) > 1;
    }
    public function has_pictures(): bool {
        return count($this->pictures) > 0;
    }
    public function add_picture(ItemPicture $picture): void {
        $priority = $picture->get_priority();
        $this->pictures[$priority] = $picture;
    }
    public function is_owner(Member $user): bool {
        return $this->owner->get_id() == $user->get_id();
    }
    public function is_first_priority(ItemPicture $pic): bool {
        $item_pictures_priorities = array_keys($this->pictures);
        $first_priority = min($item_pictures_priorities);
        return $pic->get_priority() == $first_priority;
    }
    public function is_last_priority(ItemPicture $pic): bool {
        $item_pictures_priorities = array_keys($this->pictures);
        $first_priority = max($item_pictures_priorities);
        return $pic->get_priority() == $first_priority;
    }
    public function get_picture(int $priority): ItemPicture|false {
        return $this->pictures[$priority] ?? false;
    }
    public function get_next_priority(): int {
        $next_priority = 1;
        $priorities = array_keys($this->db_get_pictures());

        if (!empty($priorities))
            $next_priority = max($priorities) + 1;

        return $next_priority;
    }
    public function get_categories_name_for_JS(): string {
        //https://stackoverflow.com/questions/16223786/store-and-retrieve-javascript-arrays-into-and-from-html-data-attributes
        $categories_name = '';
        foreach($this->categories as $category) {
            /** @var Category $category */
            $categories_name .= '"' . $category->get_name() . '"' . ",";
        }
        $categories_name = substr($categories_name, 0, -1);
        return $categories_name;
    }

    private function get_main_picture(): ItemPicture|false {
        if (empty($this->pictures))
            return false;
        $first = min(array_keys($this->pictures));
        return $this->pictures[$first];
    }

    public function get_main_picture_path(): string|false {
        $main_picture = $this->get_main_picture();

        if ($main_picture)
            $picture_path = $main_picture->get_picture_path();
        else
            $picture_path = false;

        return $picture_path;
    }

    public function get_main_thumbnail_path(): string|false {
        $main_thumbnail = $this->get_main_picture();

        if ($main_thumbnail)
            $thumbnail_path = $main_thumbnail->get_picture_path();
        else
            $thumbnail_path = false;

        return $thumbnail_path;
    }

    public static function get_item(int $id): Item|false {
        /*
        doc 'A' : https://www.reddit.com/r/SQL/comments/10ytngc/how_do_i_join_two_tables_with_all_fields_from/
        doc 'B' : https://stackoverflow.com/questions/1921421/get-the-first-element-of-an-array
        doc 'C' : https://www.php.net/manual/en/backedenum.from.php
        */
        $query = self::execute(
            "SELECT items.id AS item_id, items.title, items.created_at, items.duration_days,
            items.description, items.buy_now_price, items.starting_bid,
            users.id AS user_id, users.full_name, users.email, users.pseudo,
            users.password, users.role, users.picture_path AS user_picture, users.iban,
            item_pictures.priority, item_pictures.picture_path AS item_picture,
            c.id AS c_id, c.name, c.priority AS c_priority
                FROM items
                LEFT JOIN item_pictures ON items.id = item_pictures.item
                LEFT JOIN item_categories ic ON  ic.item = items.id
                LEFT JOIN categories c ON c.id = ic.category
                JOIN users ON items.owner = users.id
                WHERE items.id = :id",
            ["id" => $id]
        );


        if ($query->rowCount() == 0)
            return false;
        else {
            $data = $query->fetchAll();

            // On prend la première ligne pour construire l'Item (les données sont les mêmes pour l'item)
            $row = $data[0];

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
                $owner,
                $row['created_at'],
                $row['duration_days'],
                [],
                [],
                $row['description'],
                $row['buy_now_price'],
                $row['starting_bid']
            );

            // Maintenant on peut créer les images en passant l'objet $item
            $item_pictures = [];
            foreach ($data as $row) {
                if ($row['item_picture'] !== null) {
                    $item_pictures[$row['priority']] = new ItemPicture(
                        $item,
                        $row['priority'],
                        $row['item_picture']
                    );
                }
            }

            // Maintenant on peut créer les images en passant l'objet $item
            /*$categories = [];
            foreach ($data as $row) {
                // ??= assign a value only if there are none present yet.
                $categories[$row['c_priority']] ??= new Category(
                    $row['c_id'],
                    $row['name'],
                    $row['c_priority']
                );
            }*/

            Category::set_item_categories($item);

            // On attache les images à l'item
            $item->set_pictures($item_pictures);
            //$item->set_categories($categories);

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
            DELETE FROM item_categories AS ic WHERE ic.item = :item_id;
            DELETE FROM items WHERE items.id = :item_id;",
            ['item_id' => $this->id]
        );
    }

    public function persist_item_pictures(): void {
        /*
        doc: https://stackoverflow.com/questions/36766162/sql-query-insert-but-only-if-record-doesnt-already-exist
        */
        $params = ['item' => $this->id];
        $placeholders = [];

        $db_pictures_priorities = array_keys($this->db_get_pictures());

        foreach ($this->pictures as $priority => $pic) {
            /** @var ItemPicture $pic */
            if (!in_array($priority, $db_pictures_priorities)) {
                $placeholders[] = "(:item, :priority$priority, :pic_path$priority)";
                $params["priority$priority"] = $priority;
                $params["pic_path$priority"] = $pic->get_picture_path();
            }
        }

        self::execute(
            "INSERT INTO item_pictures(item, priority, picture_path)
            VALUES " . implode(',', $placeholders) . ";",
            $params
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
                    $owners[$row['owner']],
                    $row['created_at'],
                    $row['duration_days'],
                    [],
                    [],
                    //$categories[$row['id']],
                    $row['description'],
                    $row['buy_now_price'],
                    $row['starting_bid']
                );
            }

            Item::set_items_pictures($items);
            Category::set_items_categories($items);
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
                $owner[$row['owner']],
                $row['created_at'],
                $row['duration_days'],
                [],
                [],
                $row['description'] ?? "",
                $row['buy_now_price'],
                $row['starting_bid']
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

        $items_pictures = ItemPicture::get_items_picture($items);
        foreach ($items as $id => $item) {
            if (isset($items_pictures[$id]))
                $item->set_pictures($items_pictures[$id]);
        }

        Category::set_items_categories($items);

        return $available_items;
    }

    private static function set_items_pictures(array $items): void {
        /*
        doc: https://www.php.net/manual/en/function.array-keys.php
        doc: https://www.daniweb.com/programming/web-development/threads/266686/how-to-use-an-php-array-in-an-sql-query
        */
        $placeholders = [];
        $params = [];
        foreach ($items as $i => $u) {
            $placeholders[] = ":u$i";
            $params[":u$i"] = $u->get_id();
        }

        $query = self::execute(
            "SELECT *
            FROM item_pictures
            WHERE item IN ("
                . implode(',', $placeholders) .
                ")",
            $params
        );

        $data = $query->fetchAll();
        foreach ($data as $row) {
            $picture = new ItemPicture(
                $items[$row['item']],
                $row['priority'],
                $row['picture_path']
            );

            $items[$row['item']]->add_picture($picture);
        }
    }

    public function validate_title(): array {
        $errors = [];

        if (
            mb_strlen($this->title) < Configuration::get("title_min_length", 3)
            || mb_strlen($this->title) > Configuration::get("title_max_length", 255)
        ) {
            $errors["title"] = "Title length must be between 3 and 255 characters";
        }

        return $errors;
    }

    public function validate_description(): array {
        $errors = [];

        if($this->description !== null && $this->description != '' &&
            mb_strlen($this->description) < Configuration::get("description_min_length", 3)
            || mb_strlen($this->description) > Configuration::get("description_max_length", 10000)
        ) {
            $errors["description"] = "Description length must be between 3 and 10000 characters";
        }

        return $errors;
    }

    public function validate_date(): array {
        $errors = [];

        if($this->duration_days < Configuration::get("sale_duration_min", 1)
            || $this->duration_days > Configuration::get("sale_duration_max", 365)
        ) {
            $errors["days"] = "Sale duration must be between 1 and 365";
        }

        return $errors;
    }

    public function validate_prices(bool $has_sale_price = false): array {
        $errors = [];

        if ($this->buy_now_price === null && $this->starting_bid === null && !$has_sale_price) {
            $errors["sale_price"] = "An item can't be free";
        }

        if ($this->starting_bid !== null && $this->starting_bid <= 0) {
            $errors["starting_bid"] = "Starting bid must be greater than 0.";
        } if ($this->buy_now_price !== null && $this->buy_now_price <= 0) {
            $errors["sale_price"] = "Buy now price must be greater than 0.";
        }

        if ($this->starting_bid !== null && $this->buy_now_price !== null
            && $this->buy_now_price <= $this->starting_bid) {
            $errors["buy_now_price"] = "Buy now price must be greater than starting bid.";
        } if ($this->buy_now_price !== null && $this->starting_bid === null && !$has_sale_price) {
            $errors["starting_bid_missing"] = "Instant Purchase Price requires a Starting Bid.";
        }

        if ($this->starting_bid !== null && $this->buy_now_price !== null && $has_sale_price) {
            $errors["sale_option_conflict"] = "You cannot use both 'Starting Bid' and 'Sale Price'.";
        } else if ($this->starting_bid !== null && $has_sale_price) {
            $errors["sale_option_conflict"] = "You cannot use both 'Starting Bid' and 'Sale Price'.";
        } else if ($this->buy_now_price !== null && $this->starting_bid && $has_sale_price) {
            $errors["sale_option_conflict"] = "You cannot use both 'Instant Purchase Price' and 'Sale Price'.";
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

    public function db_get_pictures(): array {
        $pictures = [];

        $query = self::execute(
            "SELECT p.item, p.priority, p.picture_path
            FROM item_pictures AS p
            WHERE p.item = :item_id
            ORDER BY p.priority ASC",
            ["item_id" => $this->id]
        );

        $data = $query->fetchAll();

        foreach ($data as $row) {
            $pictures[$row['priority']] = new ItemPicture(
                $this,
                $row['priority'],
                $row['picture_path']
            );
        }

        return $pictures;
    }

    public function validate_unicity(): array {
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
            $errors["title"] = "Item title already exists in your current sales.";
        }

        return $errors;
    }

    public function equal($other_item): bool {
        return $this->id === $$other_item->get_id();
    }
}
