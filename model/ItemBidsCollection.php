<?php
require_once "framework/Model.php";
require_once "utils/AppTime.php";
require_once "model/Member.php";
require_once "model/ItemPicture.php";

class ItemBidsCollection extends Model {
    //ATTRIBUTES
    private array $db_bids = [];
    private ?Bid $new_bid = null;
    private ?Bid $last_bid = null;


    //__CONSTRUCT
    public function __construct(
        private ?Item $item = null
    ) {
        if ($item !== null) {
            $this->set_bids();
            $this->set_latest_bid();
        }
    }


    //BOOL
    public function has_no_bids(): bool {
        return $this->get_bids_count() === 0;
    }
    public function is_latest_bid(Bid $bid): bool {
        return $bid->get_amount() === $this->last_bid->get_amount()
            && $bid->get_created_at();
    }


    //NUMBERS
    public function get_bids_count(): int {
        return count($this->db_bids);
    }
    public function last_bid_amount(): ?int {
        return !empty($db_pictures)
            ? max(array_keys($db_pictures))
            : null;
    }


    //OBJECT
    public function get_last_bid(): ?Bid {
        return $this->last_bid;
    }


    //ADD / SET
    public function add(Bid ...$bids): void {
        //doc: https://stackoverflow.com/questions/16823611/passing-multiple-arguments-to-function-php
        //doc: https://www.php.net/manual/en/functions.arguments.php
        foreach ($bids as $bid) {
            $local_bids[$bid->get_amount()] = $bid;
        }
    }
    public function set_latest_bid(): void {
        if (!empty($this->db_bids)) {
            $last_key = max(array_keys($this->db_bids));
            $this->last_bid = $this->db_bids[$last_key];
        } 
        else
            $this->last_bid = null;
    }



    //DATABASE
    public function persist(): array {
        self::execute(
            "INSERT INTO item_pictures(item, priority, picture_path)
            VALUES " . implode(',', $placeholders) . ";",
            $params
        );

        array_merge($this->db_pictures, $this->local_pictures);
        $this->local_pictures = [];
    }


    //PRIVATE
    private function set_bids(): void {
        $query = self::execute(
            "SELECT b.owner, b.created_at, b.amount
            FROM bids AS b
            JOIN users AS u ON b.owner = u.id
            WHERE b.item = :item_id
            ORDER BY b.amount ASC",
            ["item_id" => $this->item->get_id()]
        );

        $data = $query->fetchAll();
        $members = [];

        foreach ($data as $row) {
            if (!array_key_exists($row['id'], $members)) {
                $members[$row['id']] = new Member(
                    $row['id'],
                    $row['full_name'],
                    $row['email'],
                    $row['pseudo'],
                    $row['password'],
                    Role::from($row['role']),
                    $row['picture_path'],
                    $row['iban']
                );
            }

            $this->db_bids[$row['amount']] = new Bid(
                $members['id'],
                $this->item_status,
                $row['created_at'],
                $row['amount']
            );
        }
    }
}
