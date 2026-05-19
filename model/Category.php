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
}


