<?php
require_once "framework/Model.php";
require_once "utils/AppTime.php";
require_once "model/Item.php";


class ItemStatus extends Model {

    public function __construct(
        private Item $item,
        private string $end_at,
        private int $bid_count,
        private ?float $max_bid,
        private bool $is_direct_sale,
        private bool $is_auction,
        private bool $has_buy_now,
        private bool $has_bids,
        private bool $buy_now_reached,
        private bool $not_purchased_direct_sale
    ) {
    }

    public function get_item(): Item {
        return $this->item;
    }
    public function get_end_at(): string {
        return $this->end_at;
    }
    public function get_bid_count(): int {
        return $this->bid_count;
    }
    public function get_max_bid(): ?float {
        return $this->max_bid;
    }
    public function get_is_direct_sale(): bool {
        return $this->is_direct_sale;
    }
    public function get_is_auction(): bool {
        return $this->is_auction;
    }
    public function get_has_buy_now(): bool {
        return $this->has_buy_now;
    }
    public function get_has_bids(): bool {
        return $this->has_bids;
    }
    public function get_buy_now_reached(): bool {
        return $this->buy_now_reached;
    }
    public function get_not_purchased_direct_sale(): bool {
        return $this->not_purchased_direct_sale;
    }

    public static function get_item_status(int $id) : Item|false {
        $query = self::execute(
            "SELECT * FROM v_items_status
            WHERE id = :id", 
            ["id" => $id]
        );

        if($query->rowCount() == 0)
            return false;
        else {
            $row = $query->fetch();
            $item = new Item(
                $row['id'], $row['title'], Member::get_member($row['owner']), $row['created_at'], 
                $row['duration_days'], $row['description'], $row['buy_now_price'], $row['starting_bid']);
            return $item;
        }
    }

    public static function get_all_available_items() : array {
        $query = self::execute(
            "SELECT * FROM v_items_status
            WHERE end_at > :now
            ORDER BY end_at ASC", 
            ["now" => AppTime::get_current_datetime()]);
        $data = $query->fetchAll();
        $available_items = [];
        foreach ($data as $row) {
            $available_items[] = new ItemStatus(
                Item::get_item($row['id']), $row['end_at'], $row['bid_count'], $row['max_bid'], $row['is_direct_sale'], 
                $row['is_auction'], $row['has_buy_now'], $row['has_bids'], $row['buy_now_reached'], $row['not_purchased_direct_sale']);
        }
        return $available_items;
    }
}