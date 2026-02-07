<?php
require_once "framework/Model.php";
require_once "utils/AppTime.php";
require_once "model/Member.php";
require_once "model/ItemPicture.php";

class ItemPicturesCollection extends Model {
    //ATTRIBUTES
    private array $db_pictures;
    private array $local_pictures;
    private ?ItemPicture $main_picture;
    private ?ItemPicture $selected_picture;


    //__CONSTRUCT
    public function __construct(
        private Item $item
    ) {
        $this->set_pictures();
        $this->set_main_picture();
        $this->set_selected_picture($this->main_picture);
    }


    //OBJECT
    public function get_picture(int $priority): ItemPicture|false {
        return $this->db_pictures[$priority] ?? false;
    }
    public function get_main_picture(): ?ItemPicture {
        $this->set_main_picture();
        return $this->main_picture;
    }
    public function get_selected_picture(int $priority): ?ItemPicture {
        return $this->selected_picture;
    }


    //BOOL
    public function has_no_pictures(): bool {
        return $this->get_picture_count() === 0;
    }
    public function has_single_picture(): bool {
        return $this->get_picture_count() === 1;
    }
    public function has_many_pictures(): bool {
        return $this->get_picture_count() > 1;
    }
    public function is_last_priority(ItemPicture $pic): bool {
        return $pic->get_priority() === $this->last_priority();
    }


    //NUMBERS
    public function get_picture_count(): int {
        //no local pictures since they're not validated yet
        return count($this->db_pictures);
    }
    public function first_priority(): int {
        return !empty($db_pictures)
            ? min(array_keys($db_pictures))
            : 0;
    }
    public function last_priority(): int {
        return !empty($db_pictures)
            ? max(array_keys($db_pictures))
            : 0;
    }


    //ADD / SET
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
    public function set_main_picture(): void {
        !empty($this->db_pictures)
            ? $this->main_picture = $this->db_pictures[$this->first_priority()]
            : null;
    }
    public function set_selected_picture(ItemPicture $pic): void {
        if (in_array($pic, $this->db_pictures))
            $this->selected_picture = $pic;
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
    public function update(): void {
        $this->set_pictures();
        $this->set_main_picture();
        $this->set_selected_picture($this->main_picture);
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
