<?php

require_once "framework/Model.php";
require_once "model/Item.php";
require_once "model/Categories.php";

class ItemCategories extends Model {
    public function __construct(
        private Item $item,
        private Categories $category
    ) { }
    public function get_item(): Item {
        return $this->item;
    }
    public function get_category(): Categories {
        return $this->category;
    }
    public function set_item(Item $new_item): void {
        $this->item = $new_item;
    }
    public function set_category(Categories $new_category): void {
        $this->category = $new_category;
    }
    public static function get_categories_by_item(Item $item): array {
        $query = self::execute(
            "SELECT categories.*
            FROM categories
            JOIN item_categories ON categories.id = item_categories.category
            WHERE item_categories.item = :item_id
            ORDER BY categories.name ASC",
            ["item_id" => $item->get_id()]
        );

        $data = $query->fetchAll();
        $results = [];

        foreach ($data as $row) {
            $results[] = new Categories(
                (int)$row["id"],
                $row["name"],
                (int)$row["priority"]
            );
        }
        return $results;
    }
}