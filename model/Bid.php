<?php
require_once "framework/Model.php";
require_once "model/Member.php";
require_once "model/Item.php";
require_once "model/ItemStatus.php";


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
    public function get_is_highest_bid(): bool {
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

    public static function get_bids_for_item(int $item_id): array {
        $query = self::execute(
            "SELECT 
            b.amount,
            b.created_at,
            u.id AS user_id,
            u.pseudo,
            u.full_name,
            u.email,
            u.password,
            u.role,
            u.picture_path,
            u.iban
         FROM bids b
         JOIN users u ON b.owner = u.id
         WHERE b.item = :item_id
         ORDER BY b.created_at DESC ",
            ["item_id" => $item_id]
        );

        $rows = $query->fetchAll();

        $item_status = ItemStatus::get_item_status($item_id);
        $max_bid = $item_status ? $item_status->get_max_bid() : null;

        $bids = [];
        foreach ($rows as $row) {
            $owner = new Member(
                $row['user_id'],
                $row['full_name'],
                $row['email'],
                $row['pseudo'],
                $row['password'],
                Role::from($row['role']),
                $row['picture_path'],
                $row['iban']
            );

            $bids[] = new Bid(
                $owner,
                $item_status,
                $row['created_at'],
                (float)$row['amount'],
                $max_bid ?? 0
            );
        }

        return $bids;
    }

    public static function validate_bid(Member $member, ItemStatus $item_status, ?float $amount): array {
        $errors = [];

        // Prevent bidding if Buy Now is reached
        if ($item_status->get_buy_now_reached()) {
            $errors[] = "Bidding is no longer allowed, the item has been bought via Buy Now.";
        }

        if (!$item_status->get_is_auction()) {
            $errors[] = "This item is not open for bidding.";
        }

        if ($amount === null || !is_numeric($amount)) {
            $errors[] = "Invalid bid amount.";
        } else {
            $newItemStatus = ItemStatus::get_item_status($item_status->get_item()->get_id());
            $currentMax = $newItemStatus->get_max_bid() ?? $item_status->get_item()->get_starting_bid();
            if ($amount <= $currentMax) {
                $errors[] = "Your bid must be higher than the current bid (€$currentMax).";
            }
        }

        return $errors;
    }


    public static function place_bid(Member $member, ItemStatus $item_status, float $amount): void {
        $created_at = date("Y-m-d H:i:s"); // timestamp

        try {
            self::execute(
                "INSERT INTO bids (owner, item, amount, created_at) VALUES (:owner, :item, :amount, :created_at)",
                [
                    "owner" => $member->get_id(),
                    "item" => $item_status->get_item()->get_id(),
                    "amount" => $amount,
                    "created_at" => $created_at
                ]
            );
        } catch (\PDOException $e) {
            // optional: log the error instead of showing it
            error_log("SQL error: " . $e->getMessage());
            throw $e; // or handle it gracefully
        }
    }




}
