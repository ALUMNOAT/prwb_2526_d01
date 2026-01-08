<?php
/*
doc: https://www.php.net/substr_replace

*/
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
    public function get_thumbnail_path(): string {
        /*
        doc: https://stackoverflow.com/questions/4636166/only-variables-should-be-passed-by-reference
        */
        $decomposed_path = explode(".", $this->picture_path);
        $extension_size = strlen(end($decomposed_path))+1;
        $thumbnail = substr_replace($this->picture_path, "_thumbnail", -$extension_size, -$extension_size);
        return $thumbnail;
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

    public static function get_items_picture(array $items): array {
        /*
        doc: https://www.php.net/manual/en/function.array-keys.php
        doc: https://www.daniweb.com/programming/web-development/threads/266686/how-to-use-an-php-array-in-an-sql-query
        */
        if(empty($items))
            return [];
        
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
            $items_pictures[$row['item']][] = new ItemPicture(
                $items[$row['item']],
                $row['priority'],
                $row['picture_path']
            );
        }

        return $items_pictures;
    }
}
