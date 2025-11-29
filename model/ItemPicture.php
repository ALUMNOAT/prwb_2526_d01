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
}
