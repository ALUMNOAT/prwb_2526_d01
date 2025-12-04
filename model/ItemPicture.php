<?php
require_once "framework/Model.php";
require_once "model/Item.php";


class ItemPicture extends Model {

    public function __construct(
        private Item $item,
        private int $priority,
        private string $picture_path,
    ) {
    }

    public function get_item(): Item {
        return $this->item;
    }
    public function get_priority(): int {
        return $this->priority;
    }
    public function get_picture_path(): string {
        return $this->picture_path;
    }

    public function set_item(Item $new_item): void {
        $this->item = $new_item;
    }
    public function set_priority(int $new_priority): void {
        $this->priority = $new_priority;
    }
    public function set_picture_path(string $new_picture_path): void {
        $this->picture_path = $new_picture_path;
    }

    public static function get_item_pictures(Item $item): array|false {
        $query = self::execute(
            "SELECT * FROM item_pictures
            WHERE id = :id
            ORDER BY priority ASC", 
            ["id" => $item->get_id()]
        );

        $data = $query->fetchAll();
        $item_pictures = [];
        foreach ($data as $row) {
            $item_pictures[] = new ItemPicture(
                Item::get_item($row['id']),
                $row['priority'],
                $row['picture_path']
            );
        }

        return $item_pictures;
    }

    public static function get_main_picture(Item $item): ItemPicture|false {
        $query = self::execute(
            "SELECT * FROM item_pictures
            WHERE item = :id
            AND priority = 1", //simplicity at its best 
            ["id" => $item->get_id()]
        );

        if($query->rowCount() == 0)
            return false;
        else {
            $row = $query->fetch();
            $item_picture = new ItemPicture(
                $item, //should I use the variable $item or call the static method ?
                $row['priority'], $row['picture_path']);
            return $item_picture;
        }
    }
}
