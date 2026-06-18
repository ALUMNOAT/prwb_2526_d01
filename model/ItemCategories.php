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

    public static function delete_categories_for_item(Item $item): void {
        self::execute(
            "DELETE FROM item_categories WHERE item = :item_id",
            ["item_id" => $item->get_id()]
        );
    }

    public static function delete_category_of_an_item(int $item_id, int $category_id): void {
        self::execute(
            "DELETE FROM item_categories WHERE item = :item_id AND category = :category_id",
            ["item_id" => $item_id,
            "category_id" => $category_id]
        );
    }

    public static function persist(int $item_id, int $category_id): void {
        self::execute(
            "INSERT INTO item_categories (item, category) VALUES (:item_id, :category_id)",
            ["item_id" => $item_id, "category_id" => $category_id]
        );
    }

    public static function validate_categories_count(array $categories_post): array {
        $errors = [];
        if (count($categories_post) > Configuration::get("category_per_item_max_count", 3)) {
            $errors["categories[]"] = "You can select up to 3 categories only.";
        }
        
        return $errors;
    }
}