<?php
require_once "framework/Model.php";
require_once "utils/AppTime.php";
require_once "model/Member.php";
require_once "model/ItemPicture.php";

class ItemPicturesCollection extends Model {
    //ATTRIBUTES
    private array $db_pictures = [];
    private array $local_pictures = [];

    //__CONSTRUCT
    public function __construct(
        private Item $item,
    ) {
    }


    //GET
    public function get_pictures() : array {
        return $this->db_pictures;
    }
    public function get_picture(int $priority): ItemPicture|false {
        return $this->db_pictures[$priority] ?? false;
    }
    public function get_main_picture(): ?ItemPicture {
        return $this->get_first_priority() === 0 
            ? null
            : $this->db_pictures[$this->get_first_priority()];
    }
    public function get_picture_count(): int {
        //no local pictures since they're not validated yet
        return count($this->db_pictures);
    }
    public function get_next_priority(): int {
        return $this->get_last_priority() + 1;
    }
    public function get_first_priority(): int {
        return !empty($db_pictures)
            ? min(array_keys($db_pictures))
            : 0;
    }
    public function get_last_priority(): int {
        return !empty($db_pictures)
            ? max(array_keys($db_pictures))
            : 0;
    }
    public function get_all_pictures(): array {
        return $this->db_pictures;
    }


    //BOOL
    public function has_no_pictures(): bool {
        return $this->get_picture_count() === 0;
    }
    public function has_single_picture(): bool {
        return $this->get_picture_count() === 1;
    }
    public function has_pictures(): bool {
        return $this->get_picture_count() >= 1;
    }
    public function has_many_pictures(): bool {
        return $this->get_picture_count() > 1;
    }
    public function is_first_priority(ItemPicture $pic): bool {
        return $pic->get_priority() === $this->get_first_priority();
    }
    public function is_last_priority(ItemPicture $pic): bool {
        return $pic->get_priority() === $this->get_last_priority();
    }


    //ADD
    public function add(ItemPicture ...$pictures): void {
        //doc: https://stackoverflow.com/questions/16823611/passing-multiple-arguments-to-function-php
        //doc: https://www.php.net/manual/en/functions.arguments.php
        $next_priority = $this->get_last_priority() + 1;

        foreach ($pictures as $pic) {
            $pic->set_priority($next_priority);
            $local_pictures[$next_priority] = $pic;
            $next_priority++;
        }
    }


    //DATABASE
    public function persist(): void {
        //doc: https://stackoverflow.com/questions/36766162/sql-query-insert-but-only-if-record-doesnt-already-exist

        $params = ['item_id' => $this->item->get_id()];
        $placeholders = [];

        foreach ($this->local_pictures as $priority => $pic) {
            $placeholders[] = "(:item_id, :priority_$priority, :pic_path_$priority)";
            $params["priority_$priority"] = $priority;
            $params["pic_path_$priority"] = $pic->get_picture_path();
        }

        self::execute(
            "INSERT INTO item_pictures(item, priority, picture_path)
            VALUES " . implode(',', $placeholders) . ";",
            $params
        );

        array_merge($this->db_pictures, $this->local_pictures);
        $this->local_pictures = [];
    }

    public static function get_item_pictures_from_db(Item $item): ItemPicturesCollection {
        $pictures = new ItemPicturesCollection($item);
        $query = self::execute(
            "SELECT *
            FROM item_pictures AS p
            WHERE p.item = :id",
            ["id" => $item->get_id()]
        );

        $data = $query->fetchAll();
        foreach ($data as $row) {
            $item_picture = ItemPicture::get_item_picture_from_row($row, $item);
            $pictures->add($item_picture);
        }

        return $pictures;
    }

    public static function set_items_pictures_from_db(array $items): void {
        /*
        doc: https://www.php.net/manual/en/function.array-keys.php
        doc: https://www.daniweb.com/programming/web-development/threads/266686/how-to-use-an-php-array-in-an-sql-query
        */
        //$items_pictures = [];

        if (!empty($items)) {
            $placeholders = [];
            $params = [];
            foreach ($items as $id => $item) {
                $placeholders[] = ":u$id";
                $params[":u$id"] = $item->get_id();
                //$items_pictures[$id] = $item->get_picture_();
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
                $item = $items[$row['item']];
                $item_picture = ItemPicture::get_item_picture_from_row($row, $item);
                $item->get_pictures_()->add($item_picture);
            }
        }
    }
}
