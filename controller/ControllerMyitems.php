<?php
require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/ItemStatus.php";
require_once "utils/NumberConverter.php";


class ControllerMyitems extends Controller {
    /*
    doc: https://php.watch/versions/8.1/spread-operator-string-array-keys
    */
    public function index(): void {
        $user = $this->get_user_or_redirect_guess();
        $my_items = $user->get_my_items();
        (new View('my_items'))->show(array_merge(
            ['user' => $user],
            $my_items
        ));
    }

    public function sales(): void {
        /*
        doc: https://stackoverflow.com/questions/30626785/php-most-frequent-value-in-array
        doc: https://stackoverflow.com/questions/1461348/return-index-of-highest-value-in-an-array
        */
        $user = $this->get_user_or_redirect_guess();
        $my_sales = $user->get_my_sales();
        $total = 0;
        $average = 0;
        $sales_count = count($my_sales);
        $bidders = ['counts' => [], 'users' => []];

        foreach ($my_sales as $bid) {
            /** @var Bid $bid */
            $total += $bid->get_amount();

            $user = $bid->get_owner();
            $user_id = $user->get_id();

            if (!isset($bidders['users'][$user_id])) {
                $bidders['users'][$user_id] = $user;
                $bidders['counts'][$user_id] = 0;
            } else
                $bidders['counts'][$user_id]++;
        }

        $average = $sales_count > 0
            ? $total / $sales_count
            : '0.00';

        $loyals_id = $sales_count > 0
            ? array_keys($bidders['counts'], max($bidders['counts']))
            : [];

        $loyals = $sales_count > 0
            ? array_intersect_key($bidders['users'], array_flip($loyals_id))
            : [];

        (new View('sales'))->show([
            'sales_view' => true,
            'user' => $user,
            'sales' => $my_sales,
            'total' => NumberConverter::money_format($total),
            'average' => NumberConverter::money_format($average),
            'sales_count' => $sales_count,
            'loyals' => $loyals
        ]);
    }

    public function purchases(): void {
        /*
        doc: https://stackoverflow.com/questions/30626785/php-most-frequent-value-in-array
        doc: https://stackoverflow.com/questions/1461348/return-index-of-highest-value-in-an-array
        */
        $user = $this->get_user_or_redirect_guess();
        $my_purchases = $user->get_my_purchases();
        $total = 0;
        $average = 0;
        $purchase_count = count($my_purchases);
        $sellers = ['counts' => [], 'users' => []];

        foreach ($my_purchases as $bid) {
            /** @var Bid $bid */
            $total += $bid->get_amount();

            $user = $bid->get_item_status()->get_item()->get_owner();
            $user_id = $user->get_id();

            if (!isset($sellers['users'][$user_id])) {
                $sellers['users'][$user_id] = $user;
                $sellers['counts'][$user_id] = 0;
            } else
                $sellers['counts'][$user_id]++;
        }

        $average = $purchase_count > 0
            ? $total / $purchase_count
            : '0.00';

        $loyals_id = $purchase_count > 0
            ? array_keys($sellers['counts'], max($sellers['counts']))
            : [];

        $loyals = $purchase_count > 0
            ? array_intersect_key($sellers['users'], array_flip($loyals_id))
            : [];

        (new View('sales'))->show([
            'sales_view' => false,
            'user' => $user,
            'sales' => $my_purchases,
            'total' => NumberConverter::money_format($total),
            'average' => NumberConverter::money_format($average),
            'sales_count' => $purchase_count,
            'loyals' => $loyals
        ]);
    }

    public function delete(): void {
        $user = $this->get_user_or_redirect_guess();
        $item_id = $this->get_id_or_redirect($_GET['param1']);
        $item_status = $this->get_item_status_or_redirect($item_id, $user);   //not owner, no access

        if ($item_status->get_has_bids() || $item_status->has_sold()) {
            (new View('error'))->show(['error' => 'This item has bids, and cannot be deleted']);
        } else if (isset($_POST['confirm']) && $_POST['confirm'] === strval($item_id)) {
            $item_status->delete();
            $this->redirect('my_items');
        } else {
            (new View('delete_confirm'))->show(['item' => $item_status->get_item(), 'user' => $user]);
        }
    }

    private function get_user_or_redirect_guess(bool $guest_pass = false): Member {
        /** @var Member $user */
        $user = $this->get_user_or_redirect();
        if ($user->is_guest() && !$guest_pass)
            $this->redirect('browse');
        return $user;
    }

    private function get_id_or_redirect(mixed $id, string $controller = 'my_items', string $action = '', string $param1 = ''): int {
        if (!isset($id) || !NumberConverter::is_id($id))
            $this->redirect($controller, $action, $param1);
        $id = NumberConverter::get_id_or_false($id);
        return $id;
    }

    private function get_item_or_redirect(int $item_id, ?Member $user = null): Item {
        $item = Item::get_item($item_id);
        if (!$item || $user !== null && !$item->is_owner($user))
            $this->redirect("browse", "page", "1");
        return $item;
    }

    private function get_item_status_or_redirect(int $item_id, ?Member $user = null): ItemStatus {
        $item_status = ItemStatus::get_item_status($item_id);
        if (!$item_status || $user !== null && !$item_status->get_item()->is_owner($user))
            $this->redirect("my_items");
        return $item_status;
    }
}
