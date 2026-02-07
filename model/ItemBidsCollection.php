<?php
require_once "framework/Model.php";
require_once "utils/AppTime.php";
require_once "model/Member.php";
require_once "model/ItemPicture.php";

class ItemBidsCollection extends Model {
    //ATTRIBUTES
    private array $db_bids;
    private array $local_bids;
    private ?Bid $last_bid;
    private Item $item;


    //__CONSTRUCT
    public function __construct(
        private ItemStatus $item_status
    ) {
        $this->item = $item_status->get_item();
        $this->set_bids();
        $this->set_latest_bid();
    }


    //OBJECT
    public function get_last_bid(int $priority): ?Bid {
        return $this->last_bid;
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


    //ADD / SET
    public function add(ItemPicture ...$pictures): void {
        //doc: https://stackoverflow.com/questions/16823611/passing-multiple-arguments-to-function-php
        //doc: https://www.php.net/manual/en/functions.arguments.php
        $next_priority = $this->last_priority() + 1;

        foreach ($pictures as $pic) {
            $pic->set_priority($next_priority);
            $local_pictures[$next_priority] = $pic;
            $next_priority++;
        }
    }
    public function set_latest_bid(): void {
        !empty($this->db_bids)
            ? $this->latest_bid = $this->db_bids[$this->first_priority()]
            : null;
    }



    //DATABASE
    public function persist(): void {
        //doc: https://stackoverflow.com/questions/36766162/sql-query-insert-but-only-if-record-doesnt-already-exist

        $params = ['item_id' => $this->item->get_id()];
        $placeholders = [];

        foreach ($this->local_pictures as $priority => $pic) {
            $placeholders[] = "(:item_id, :priority_$priority, :pic_path_$priority)";
            $params["priority_$priority"] = $priority;
            $params["pic_path_$priority"] = $pic->get_picture_path();
        }

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
