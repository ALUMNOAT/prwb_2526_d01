<?php
require_once "framework/Model.php";
require_once "model/Member.php";
require_once "model/Item.php";


class Bid extends Model {

    public function __construct(
        private Member $owner,
        private ItemStatus $item_status,
        private string $created_at, //this needs to be its own class in utils ? would make it easier, string for now
        private float $amount,
        private ?float $highest_bid = null
    ) {
    }

    public function get_owner(): Member {
        return $this->owner;
    }
    public function get_item_status(): ItemStatus {
        return $this->item_status;
    }
    public function get_created_at(): string {
        return $this->created_at;
    }
    public function get_amount(): float {
        return $this->amount;
    }
    public function get_highest_bid(): ?float {
        return $this->highest_bid;
    }
    public function get_is_highest_bid(): float {
        return $this->highest_bid == $this->amount;
    }

    public function set_owner(Member $new_owner): void {
        $this->owner = $new_owner;
    }
    public function set_item_status(ItemStatus $new_item_status): void {
        $this->item_status = $new_item_status;
    }
    public function set_created_at(string $new_created_at): void {
        $this->created_at = $new_created_at;
    }
    public function set_amount(float $new_amount): void {
        $this->amount = $new_amount;
    }
    public function set_highest_bid(?float $highest_bid): void {
        $this->highest_bid = $highest_bid;
    }
}
