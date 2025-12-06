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
        private ?string $description,
        private ?float $buy_now_price,
        private ?float $starting_bid,
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
    public function set_description(?string $new_description): void {
        $this->description = $new_description;
    }
    public function set_buy_now_price(?float $new_buy_now_price): void {
        $this->buy_now_price = $new_buy_now_price;
    }
    public function set_starting_bid(?float $new_starting_bid): void {
        $this->starting_bid = $new_starting_bid;
    }

    public static function get_item(int $id): Item|false {
        /*
        doc 'A' : https://www.reddit.com/r/SQL/comments/10ytngc/how_do_i_join_two_tables_with_all_fields_from/
        doc 'B' : https://stackoverflow.com/questions/1921421/get-the-first-element-of-an-array
        doc 'C' : https://www.php.net/manual/en/backedenum.from.php
        */
        $query = self::execute(
            "SELECT * FROM items
            JOIN item_pictures ON items.id = item_pictures.item;    # 'A'
            JOIN users ON items.owner = users.id;                   # 'A'
            WHERE item.id = :id",
            ["id" => $id]
        );

        if ($query->rowCount() == 0)
            return false;
        else {
            $data = $query->fetchAll();

            //PICTURES
            $item_pictures = [];
            foreach ($data as $row) {
                $item_pictures[] = new ItemPicture(
                    Item::get_item($row['item']),
                    $row['priority'],
                    $row['picture_path']
                );
            }

            $row = array_pop($data);    # 'B', we care about efficiency here !

            //OWNER
            $owner = new Member(
                $row['full_name'],
                $row['email'],
                $row['pseudo'],
                $row['password'],
                Role::from($row['role']),
                $row['users.id'],
                $row['picture_path'],
                $row['iban']
            );
            //ITEM
            $item = new Item(
                $row['items.id'],
                $row['title'],
                $owner,
                $row['created_at'],
                $row['duration_days'],
                $item_pictures,
                $row['description'],
                $row['buy_now_price'],
                $row['starting_bid']
            );

            return $item;
        }
    }

    public function get_main_picture_path(): string {
        return ItemPicture::get_main_picture($this)->get_picture_path();
    }
}

