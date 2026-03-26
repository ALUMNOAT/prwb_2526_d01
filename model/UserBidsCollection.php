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

    public function get_bids_from_db(): array {
        /*
        doc: https://www.interviewquery.com/p/sql-max-case-when
        doc: https://sentry.io/answers/in-sql-how-do-i-select-only-rows-with-max-value-on-a-column/ --> see topchef's answer
        doc: https://stackoverflow.com/questions/38549/what-is-the-difference-between-inner-join-and-outer-join
        doc: https://stackoverflow.com/questions/15486131/select-distinct-and-max-in-the-same-query
        doc: https://stackoverflow.com/questions/4282413/sort-array-of-objects-by-one-property
        doc: https://stackoverflow.com/questions/45420576/syntax-error-column-isnt-in-group-by-sql-laravel
        doc: https://stackoverflow.com/questions/41571271/group-by-not-working-laravel
        doc: https://dev.mysql.com/doc/refman/5.7/en/group-by-handling.html
        */
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

            $my_bids[$row['item']] = new Bid(
                $this->user,
                $item,
                $row['created_at'],
                $row['amount'],
                null,
            );
        }

        if ($query->rowCount() > 0) {
            $data = $query->fetchAll();

            $items_id = array_column($data, 'item');
            $items = Item::get_items($items_id);

            $items_status = ItemStatus::get_items_status($items);

            $owners_id = array_column($data, 'owner');
            $owners = Member::get_members($owners_id);

            foreach ($data as $row) {
                $highest_bid = $row['global_max'];
                $owner = $owners[$row['owner']];
                $item_status = $items_status[$row['item']];

                $my_bids[$row['item']] = new Bid(
                    $owner,
                    $item_status,
                    $row['created_at'],
                    $row['amount'],
                    $highest_bid
                );
            }

            /*
            //If needs be to sort in PHP, I could use usort with an anonymous function :)
            usort($my_bids, function ($a, $b) {
                return [bool]
            })
                */
        }

        return $my_bids;
    }
}
