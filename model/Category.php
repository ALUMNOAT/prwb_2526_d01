<?php
require_once "framework/Model.php";
require_once "utils/AppTime.php";
require_once "model/Member.php";
require_once "model/ItemPicture.php";


class Category extends Model implements JsonSerializable {
    public function __construct(
        private ?int $id,
        private string $name,
        private int $priority,
    ) {
    }

    public function jsonSerialize() : array {
        //https://www.slingacademy.com/article/how-to-convert-an-object-to-json-in-php-serialization/
        return [
            "id" => $this->id,
            "name" => $this->name,
            "priority" => $this->priority,
        ];
    }

    public function get_id(): ?int {
        return $this->id;
    }
    public function get_name(): string {
        return $this->name;
    }
    public function get_priority(): int {
        return $this->priority;
    }

    public function set_id(int $new_id): void {
        $this->id = $new_id;
    }
    public function set_name(string $new_name): void {
        $this->name = $new_name;
    }
    public function set_priority(int $new_priority): void {
        $this->priority = $new_priority;
    }

    public static function get_categories(array $items_id) : array {
        /*
        doc: https://stackoverflow.com/questions/907806/passing-an-array-to-a-query-using-a-where-clause
        doc: https://www.daniweb.com/programming/web-development/threads/266686/how-to-use-an-php-array-in-an-sql-query
        doc A: https://www.codestudy.net/blog/default-array-values-if-key-doesn-t-exist/#method-4-null-coalescing-assignment-
        */

        $categories = [];

        if (!empty($items_id)) {
            $placeholders = [];
            $params = [];
            foreach ($items_id as $i => $item_id) {
                $placeholders[] = ":id$i";
                $params[":id$i"] = $item_id;
            }

            $query = self::execute(
                "SELECT ic.item, ic.category, c.id, c.name, c.priority
                FROM item_categories ic
                LEFT JOIN categories c ON c.id = ic.category
                WHERE ic.item IN ("
                    . implode(',', $placeholders) .
                    ")",
                $params
            );

            $data = $query->fetchAll();

            $categories = [];
            foreach ($data as $row) {
                //see 'doc A' for '??='
                //it assigns a value if there are none already
                $categories[$row['item']][$row['category']] ??= new Category(
                    $row['id'],
                    $row['name'],
                    $row['priority'],
                );
            }
        }

        return $categories;
    }

    public static function set_items_categories(array $items) : void {
        /*
        doc: https://stackoverflow.com/questions/907806/passing-an-array-to-a-query-using-a-where-clause
        doc: https://www.daniweb.com/programming/web-development/threads/266686/how-to-use-an-php-array-in-an-sql-query
        doc A: https://www.codestudy.net/blog/default-array-values-if-key-doesn-t-exist/#method-4-null-coalescing-assignment-
        */
        if (!empty($items)) {
            $placeholders = [];
            $params = [];
            foreach ($items as $i => $item) {
                $placeholders[] = ":id$i";
                $params[":id$i"] = $i;
            }

            $query = self::execute(
                "SELECT ic.item, ic.category, c.id, c.name, c.priority
                FROM item_categories ic
                LEFT JOIN categories c ON c.id = ic.category
                WHERE ic.item IN ("
                    . implode(',', $placeholders) .
                    ")",
                $params
            );

            $data = $query->fetchAll();

            $categories = [];
            foreach ($data as $row) {
                //see 'doc A' for '??='
                //it assigns a value if there are none already
                $categories[$row['item']][$row['category']] ??= new Category(
                    $row['id'],
                    $row['name'],
                    $row['priority'],
                );
            }

            foreach ($items as $item_id => $item) {
                /** @var Item $item */
                $item->set_categories($categories[$item_id]);
            }
        }
    }

}


