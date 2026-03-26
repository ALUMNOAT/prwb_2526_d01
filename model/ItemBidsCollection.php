<?php
require_once "framework/Model.php";
require_once "utils/AppTime.php";
require_once "model/Member.php";
require_once "model/ItemPicture.php";

class ItemBidsCollection extends Model {
    //ATTRIBUTES
    private array $bids = [];

    //__CONSTRUCT
    public function __construct(
        private Item $item
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


    public static function get_item_bids_from_db(Item $item): ItemBidsCollection {
        $bids = new ItemBidsCollection($item);
        $query = self::execute(
            "SELECT *
            FROM bids
            WHERE bids.item = :id",
            ["id" => $item->get_id()]
        );

        $data = $query->fetchAll();
        $owners_ids = array_column($data, 'owner');
        $owners = Member::get_members($owners_ids);

        foreach ($data as $row) {
            $bid = new Bid(
                $owners[$row["owner"]],
                $item,
                $row["created_at"],
                $row["amount"],
            );
            $bids->add($bid);
        }

        return $bids;
    }
    
}