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

    public static function get_item_status(int $id): ItemStatus|false {
        $query = self::execute(
            "SELECT * FROM v_items_status
            WHERE id = :id",
            ["id" => $id]
        );

        if ($query->rowCount() == 0)
            return false;
        else {
            $row = $query->fetch();
            $item_status = new ItemStatus(
                Item::get_item($row['id']),
                $row['end_at'],
                $row['bid_count'],
                $row['max_bid'],
                $row['is_direct_sale'],
                $row['is_auction'],
                $row['has_buy_now'],
                $row['has_bids'],
                $row['buy_now_reached'],
                $row['not_purchased_direct_sale']
            );

            return $item_status;
        }
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
                $items_status[$row['id']] = new ItemStatus(
                    $items[$row['id']],
                    $row['end_at'],
                    $row['bid_count'],
                    $row['max_bid'],
                    $row['is_direct_sale'],
                    $row['is_auction'],
                    $row['has_buy_now'],
                    $row['has_bids'],
                    $row['buy_now_reached'],
                    $row['not_purchased_direct_sale']
                );
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
            $available_items[] = new ItemStatus(
                Item::get_item($row['id']),
                $row['end_at'],
                $row['bid_count'],
                $row['max_bid'],
                $row['is_direct_sale'],
                $row['is_auction'],
                $row['has_buy_now'],
                $row['has_bids'],
                $row['buy_now_reached'],
                $row['not_purchased_direct_sale']
            );
        }
        return $available_items;
    }

    public function ends_in(int $precision = 2): string {
        /*
        doc: //https://stackoverflow.com/questions/1416697/converting-timestamp-to-time-ago-in-php-e-g-1-day-ago-2-days-ago/18602474#18602474
        this function has a major flaw, which is that it doesn't take care of negative 
        time scale values... but that's maybe outside the scope of this project for now
        */

        $now = new DateTime(AppTime::get_current_datetime());
        $ago = new DateTime($this->end_at);
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
