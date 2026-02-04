<?php
require_once "framework/Model.php";
require_once "utils/AppTime.php";
require_once "model/Member.php";
require_once "model/ItemPicture.php";

class ItemPicturesCollection extends Model {

    private array $db_pictures;
    private array $local_pictures;


    public function __construct(
        private Item $item
    ) {
        $this->set_pictures();
    }

    public function last_priority(): int {
        $last_pictures_added = empty($local_pictures)
            ? $this->db_pictures
            : $this->local_pictures;

        $priorities = array_keys($last_pictures_added);
        return empty($priorities)
            ? 0
            : max($priorities);
    }

    public function add(ItemPicture ...$pictures): void {
        //doc: https://stackoverflow.com/questions/16823611/passing-multiple-arguments-to-function-php
        //doc: https://www.php.net/manual/en/functions.arguments.php
        $next_priority = $this->last_priority() + 1;
        
        foreach ($pictures as $pic) {
            $pic->set_priority($next_priority);
            $local_pictures[$next_priority] = $pic;
            $next_priority++;
        }
    }

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

    private function set_pictures(): void {
        $query = self::execute(
            "SELECT p.priority, p.picture_path
            FROM item_pictures AS p
            WHERE p.item = :item_id
            ORDER BY p.priority ASC",
            ["item_id" => $this->item->get_id()]
        );

        $data = $query->fetchAll();

        foreach ($data as $row) {
            $this->db_pictures[$row['priority']] = new ItemPicture(
                $this->item,
                $row['priority'],
                $row['picture_path']
            );
        }
    }
}
