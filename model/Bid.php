<?php
require_once "framework/Model.php";
require_once "model/Member.php";
require_once "model/Item.php";


class Bid extends Model {

    public function __construct(
        private Member $owner,
        private Item $item,
        private string $created_at, //this needs to be its own class in utils ? would make it easier, string for now
        private float $amount,
    ) {
    }

    public function get_owner(): Member {
        return $this->owner;
    }
    public function get_item(): Item {
        return $this->item;
    }
    public function get_created_at(): string { // /!\
        return $this->created_at;
    }
    public function get_amount(): int {
        return $this->amount;
    }

    public function set_owner(Member $new_owner): void {
        $this->owner = $new_owner;
    }
    public function set_item(Item $new_item): void {
        $this->item = $new_item;
    }
    public function set_created_at(string $new_created_at): void { // /!\
        $this->created_at = $new_created_at;
    }
    public function set_amount(int $new_amount): void {
        $this->amount = $new_amount;
    }
}
