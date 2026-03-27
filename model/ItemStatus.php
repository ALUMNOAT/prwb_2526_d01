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

    public function is_owner(Member $user): bool {
        return $this->item->is_owner($user);
    }

    public function is_biddable(Member $bidder): bool {
        return $this->is_auction
            && !$this->is_owner($bidder)
            && !$this->buy_now_reached
            && !$this->has_ended()
            && !$bidder->is_guest();
    }

    public function is_buyable(?Member $buyer): bool {
        return $this->has_buy_now
            && !$this->is_owner($buyer)
            && !$this->buy_now_reached
            && !$this->has_ended()
            && !$buyer->is_guest()
            && $this->max_bid < $this->item->get_buy_now_price();
    }

    public static function get_item_status(int $id): ItemStatus|false {
        $query = self::execute(
            "SELECT * FROM v_items_status
            WHERE id = :id",
            ["id" => $id]
        );
        $status = false;

        if ($query->rowCount() > 0) {
            $row = $query->fetch();
            $owner = Member::get_member($row["owner"]);
            $item = Item::get_item_from_row($row, $owner);
            $status = ItemStatus::get_item_status_from_row($row, $item);
        }

        return $status;
        
    }
    public static function get_items_status(array $items): array {
        $items_status = [];

        if (!empty($items)) {
            $placeholders = [];
            $params = [];
            foreach ($items as $id => $item) {
                $placeholders[] = ":u$id";
                $params[":u$id"] = $id;
            }

            $query = self::execute(
                "SELECT * 
                FROM v_items_status AS v
                WHERE v.id IN ("
                    . implode(',', $placeholders) .
                    ")",
                $params
            );

            $data = $query->fetchAll();

            foreach ($data as $row) {
                $item = $items[$row['id']];
                $status = ItemStatus::get_item_status_from_row($row, $item);
                $items_status[$row['id']] = $status;
            }
        }

        return $items_status;
    }
    public static function get_all_available_items(): array {
        $query = self::execute(
            "SELECT * FROM v_items_status
            WHERE end_at > :now
            ORDER BY end_at ASC",
            ["now" => AppTime::get_current_datetime()]
        );
        $data = $query->fetchAll();
        $available_items = [];
        foreach ($data as $row) {
            $owner = Member::get_member($row["owner"]);
            $item = Item::get_item_from_row($row, $owner);
            $status = ItemStatus::get_item_status_from_row($row, $item);
            $available_items[] = $status;
        }
        return $available_items;
    }
    public static function get_item_status_from_row(array $row, Item $item, 
        String $end_at = "end_at", String $bid_count = "bid_count", String $max_bid = "max_bid", String $is_direct_sale = "is_direct_sale", 
        String $is_auction = "is_auction", String $has_buy_now = "has_buy_now", String $has_bids = "has_bids", 
        String $buy_now_reached = "buy_now_reached", $not_purchased_direct_sale = "not_purchased_direct_sale"): ItemStatus {
        return new ItemStatus(
            $item,
            $row[$end_at],
            (int)$row[$bid_count],
            (float)$row[$max_bid] ?? null,
            $row[$is_direct_sale] == 1,
            $row[$is_auction] == 1,
            $row[$has_buy_now] == 1,
            $row[$has_bids] == 1,
            $row[$buy_now_reached] == 1,
            $row[$not_purchased_direct_sale] == 1,
        );
    }

    public function ends_in(int $precision = 2): string {
        /*
        doc: //https://stackoverflow.com/questions/1416697/converting-timestamp-to-time-ago-in-php-e-g-1-day-ago-2-days-ago/18602474#18602474
        this function has a major flaw, which is that it doesn't take care of negative 
        time scale values... but that's maybe outside the scope of this project for now
        */

        $now = new DateTime(AppTime::get_current_datetime());
        $ago = new DateTime($this->end_at);

        if ($this->has_ended() || $this->has_sold() || $this->has_not_sold())
            return "ended";
        else {
            $diff = $ago->diff($now);
            $time_scale = array(
                'y' => $diff->y,
                'm' => $diff->m,
                'w' => floor($diff->d / 7), //I think it's python which has '/' and '//' hehe :)
                'd' => $diff->d % 7,
                'h' => $diff->h,
                'min' => $diff->i,
                's' => $diff->s,
            );

            //doc: https://www.php.net/manual/en/function.array-filter.php
            $test = array_filter($time_scale); //removes values equals to 0 (amazing !)
            $test = array_slice($test, 0, $precision); //takes at most the first 2 elements (or precision)
            $ends_in = "";

            foreach ($test as $k => $v) {
                $ends_in = $ends_in . $v . $k . " ";
            }

            return $ends_in === ""
                ? "now"
                : $ends_in . "left";
        }
    }

    public function has_ended(): bool {
        $now = new DateTime(AppTime::get_current_datetime());
        $end_at = new DateTime($this->end_at);
        return $now > $end_at;
    }

    public function has_sold(): bool {
        return $this->has_ended() && $this->has_bids || $this->buy_now_reached;
    }

    public function has_not_sold(): bool {
        return $this->has_ended() && !$this->has_bids && !$this->buy_now_reached;
    }

    public function delete(): void {
        /*
        doc: https://www.w3schools.com/mysql/mysql_delete.asp
        doc: https://dba.stackexchange.com/questions/232987/deleting-multiple-tables-in-a-single-delete-query-in-mysql
        doc: https://stackoverflow.com/questions/31136434/mysql-deleting-rows-based-on-a-condition-with-data-from-another-table-and-no-jo
        */
        $query = self::execute(
            "DELETE FROM item_pictures WHERE item_pictures.item = :item_id;
            DELETE FROM items WHERE items.id = :item_id;
            DELETE FROM v_items_status AS v WHERE v.id = :item_id;",
            ['item_id' => $this->item->get_id()]
        );
    }

    public static function has_bids(int $item_id): bool {
        $query = self::execute(
            "SELECT has_bids
         FROM v_items_status
         WHERE id = :id",
            ["id" => $item_id]
        );

        if ($query->rowCount() === 0) {
            return false;
        }

        return (bool)$query->fetch()['has_bids'];
    }

    public static function is_direct_sale(int $item_id): bool {
        $query = self::execute(
            "SELECT is_direct_sale
            FROM v_items_status
            WHERE id = :id",
            ["id" => $item_id]
        );
        if ($query->rowCount() === 0) {
            return false;
        }

        return (bool)$query->fetch()['is_direct_sale'];
    }

}
