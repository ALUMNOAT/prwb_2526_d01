<?php
require_once "framework/Model.php";
require_once "utils/AppTime.php";
require_once "model/Member.php";
require_once "model/ItemPicture.php";



class Item extends Model {

    public function __construct(
        private int $id, //is id needed ? yes
        private string $title,
        private Member $owner,
        private string $created_at, //this needs to be its own class in utils ? would make it easier, string for now
        private int $duration_days,
        private array $pictures = [],
        private ?string $description = null,
        private ?float $buy_now_price = null,
        private ?float $starting_bid = null,
    ) {
    }

    public function get_id(): int {
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
    public function set_description(?string $new_description): void {
        $this->description = $new_description;
    }
    public function set_buy_now_price(?float $new_buy_now_price): void {
        $this->buy_now_price = $new_buy_now_price;
    }
    public function set_starting_bid(?float $new_starting_bid): void {
        $this->starting_bid = $new_starting_bid;
    }

    public function add_picture(ItemPicture $picture) : void {
        $this->pictures[] = $picture;
    }

    public static function get_item(int $id): Item|false {
        /*
        doc 'A' : https://www.reddit.com/r/SQL/comments/10ytngc/how_do_i_join_two_tables_with_all_fields_from/
        doc 'B' : https://stackoverflow.com/questions/1921421/get-the-first-element-of-an-array
        doc 'C' : https://www.php.net/manual/en/backedenum.from.php
        */
        $query = self::execute(
            "SELECT items.id AS item_id, items.title, items.created_at, items.duration_days, items.description,
            items.buy_now_price, items.starting_bid,
            users.id AS user_id, users.full_name, users.email, users.pseudo, users.password, users.role, users.picture_path AS user_picture, users.iban,
            item_pictures.priority, item_pictures.picture_path AS item_picture
                FROM items
                LEFT JOIN item_pictures ON items.id = item_pictures.item
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
                $row['item_picture'],
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
                $row['description'],
                $row['buy_now_price'],
                $row['starting_bid']
            );

            // Maintenant on peut créer les images en passant l'objet $item
            $item_pictures = [];
            foreach ($data as $row) {
                if ($row['item_picture'] !== null) {
                    $item_pictures[] = new ItemPicture(
                        $item,
                        $row['priority'],
                        $row['item_picture']
                    );
                }
            }

            // On attache les images à l'item
            $item->set_pictures($item_pictures);

            return $item;
        }
    }

    public function get_main_picture_path(): string {
        if(empty($this->pictures))
            return false;
        $first = min(array_keys($this->pictures));
        return $this->pictures[$first]->get_picture_path();
    }

    public static function get_all_available_items(): array {
        $query = self::execute(
            "SELECT *
            FROM v_items_status AS v
            WHERE v.end_at > :now
            ORDER BY v.end_at ASC",
            [
                "now" => AppTime::get_current_datetime()
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
        foreach ($items as $item) {
            $item->set_pictures($items_pictures[$item->get_id()]);
        }

        return $available_items;
    }

    private static function set_items_pictures(array $items) : void {
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
                .implode(',', $placeholders).
            ")",
            $params
        );

        $data = $query->fetchAll();
        $items_pictures = [];
        foreach($data as $row) {
            $picture = new ItemPicture(
                $items[$row['item']],
                $row['priority'],
                $row['picture_path']
            );

            $items[$row['item']]->add_picture($picture);
        }

        
    }
}

