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
            $owner = $owners[$row["owner"]];
            $bid = Bid::get_bid_from_row($row, $owner, $item);
            $bids->add($bid);
        }

        return $bids;
    }
    public static function get_items_bids_from_db(array $items): array {
        $bids = [];

        if (!empty($items)) {
            $placeholders = [];
            $params = [];
            foreach ($items as $id => $item) {
                $placeholders[] = ":u$id";
                $params[":u$id"] = $item->get_id();
                $bids[$id] = new ItemBidsCollection($item);
            }

            $query = self::execute(
                "SELECT * 
                FROM bids
                WHERE item IN ("
                    . implode(',', $placeholders) .
                    ")",
                $params
            );
            $data = $query->fetchAll();
            
            $owners_ids = array_column($data, 'owner');
            $owners = Member::get_members($owners_ids);

            foreach ($data as $row) {
                $owner = $owners[$row["owner"]];
                $item = $items[$row["item"]];
                $bid = Bid::get_bid_from_row($row, $owner, $item);
                $bids[$row['item']]->add($bid);
            }
        }

        return $bids;
    }
    public static function get_item_bids_from_rows(array $rows, array $owners, Item $item, String $owner = "owner", String $created_at = "created_at", String $amount = "amount"): array {
        $item_bids = [];
        foreach($rows as $row) {
            $owner = $owners[$row[$owner]];
            $item_bids[$row[$amount]] = Bid::get_bid_from_row($row, $owner, $item);
        }
        return $item_bids;
    }


    public static function set_item_bids_from_db(Item $item): void {
        $bids = ItemBidsCollection::get_item_bids_from_db($item);
        $item->set_bids($bids);
    }

}