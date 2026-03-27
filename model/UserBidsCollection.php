<?php
require_once "framework/Model.php";
require_once "model/Member.php";

class UserBidsCollection extends Model {
    //ATTRIBUTES
    private array $bids = [];

    //__CONSTRUCT
    public function __construct(
        private Member $user
    ) {
    }

    public function get_bids(): array {
        return $this->bids;
    }
    public function get_bids_count(): int {
        return count($this->bids);
    }

    public function last_bid_amount(): ?int {
        return !empty($db_pictures)
            ? max(array_keys($db_pictures))
            : null;
    }
    public function has_no_bids(): bool {
        return count($this->bids) === 0;
    }

    public function add(Bid ...$bids): void {
        foreach ($bids as $bid) {
            $bids[$bid->get_amount()] = $bid;
        }
    }

    public function get_user_bids_from_db(): array {
        $query = self::execute(
            "SELECT *
            FROM bids
            WHERE bids.owner = :user_id",
            [
                "user_id" => $this->user->get_id(),
            ]
        );
        $data = $query->fetchAll();

        $items_id = array_column($data, 'item');
        $items = Item::get_items($items_id);
        $bids = [];

        foreach ($data as $row) {
            $item = $items[$row['item']];
            $bid = Bid::get_bid_from_row($row, $this->user, $item);
            $bids[$row['item']][$row['amount']] = $bid;
        }

        return $bids;
    }
}
