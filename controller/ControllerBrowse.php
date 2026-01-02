<?php
require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/ItemStatus.php";
require_once "utils/NumberConverter.php";


class ControllerBrowse extends Controller {
    public function index(): void {
        if (!$this->user_logged())
            $this->redirect('login');
        else 
            $this->redirect('browse', 'page', '1');
    }

    public function page(): void {
        /*
        doc: https://www.php.net/manual/en/function.array-intersect-key.php
        doc: https://www.php.net/manual/en/function.array-diff.php
        doc: https://www.php.net/manual/en/function.is-int.php
        doc: https://stackoverflow.com/questions/2012187/how-to-check-that-a-string-is-an-int-but-not-a-double-etc
        doc: https://stackoverflow.com/questions/4844916/best-way-to-check-for-positive-integer-php
        doc: https://www.php.net/manual/en/filter.constants.php#constant.filter-validate-bool
        doc: https://www.php.net/manual/en/language.operators.logical.php
        doc: https://www.php.net/manual/en/function.ceil.php
        doc: https://www.php.net/manual/en/function.intdiv.php
        doc: https://www.php.net/manual/en/function.array-slice.php
        */
        if (!$this->user_logged())
            $this->redirect('login');
        elseif (!isset($_GET['param1']) || !NumberConverter::is_page($_GET['param1']))
            $this->redirect('browse', 'page', '1');
        else {
            $page = NumberConverter::get_page_or_false($_GET['param1']);
            $page_content_size = Configuration::get('page_content_size', 12);   //we show 12 items on a page
            /** @var Member $user */
            $user = $this->get_user_or_redirect();

            $my_bids = $user->get_my_bids();    //returns '[]' for guests
            $available_items = Item::get_all_available_items();
            $my_available_items = array_diff_key($available_items, $my_bids);   // set theory: 'A - B' on keys (here it's item id)

            //each chunk is 12 items: chunk 1 is page 1; chunk 2 is page 2; etc...
            $my_bids = array_chunk($my_bids, $page_content_size, true);
            $my_available_items = array_chunk($my_available_items, $page_content_size, true);

            $bids_pages = count($my_bids);  //each chunk is a page, so 5 chunks = 5 pages
            $items_pages = count($my_available_items);

            if($page > $bids_pages && $page > $items_pages) {   //user entered a big number ? go to last page
                $page = strval(max($bids_pages, $items_pages));
                $this->redirect('browse', 'page', $page);
            }

            (new View("browse"))->show([
                "page" => $page,
                "user" => $user,
                "my_bids" => $my_bids[min($page, $bids_pages) - 1], //-1 for computer
                "available_items" => $my_available_items[min($page, $items_pages) - 1],
                "my_bids_pagination" => $bids_pages,
                "available_items_pagination" => $items_pages,
            ]);
        }
    }
}
