<?php
require_once "framework/Model.php";
require_once "model/Member.php";

class UserItemsCollection extends Model {
    //ATTRIBUTES
    private array $my_items = [];

    //__CONSTRUCT
    public function __construct(
        private Member $user
    ) {
    }

    public function get_user_items_from_db(): array {
        $query = self::execute(
            "SELECT *
            FROM v_items_status AS v
            WHERE owner = :user_id
            ORDER BY v.end_at ASC;",
            ["user_id" => $this->user->get_id()]
        );
        $data = $query->fetchAll();

        $items_status = [];
        $my_items = [];

    
        $data = $query->fetchAll();

        foreach ($data as $row) {
            $item = Item::get_item_from_row($row, $this->user);

            $my_items[$row['id']] = $item;
            $item_status = ItemStatus::get_item_status_from_row($row, $item);

            if ($item_status->has_sold())
                $my_items_status['closed_sold_items'][$item->get_id()] = $item_status;
            else if ($item_status->has_not_sold())
                $my_items_status['closed_unsold_items'][$item->get_id()] = $item_status;
            else
                $my_items_status['active_items'][$item->get_id()] = $item_status;
        }

        $items_pictures = ItemPicture::get_items_picture($my_items);
        foreach ($my_items as $item) {
            /** @var Item $item */
            $item_id = $item->get_id();
            $item->set_pictures($items_pictures[$item_id] ?? []);
            
        }

        return $my_items_status;
    }
}