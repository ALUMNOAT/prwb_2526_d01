<?php
require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/ItemStatus.php";
require_once "utils/NumberConverter.php";
require_once 'utils/Image.php';
require_once 'model/Bid.php';

class ControllerItem extends Controller {

    public function index(): void {
        $this->redirect();
    }

    public function browse(): void {
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
            $this->redirect('item', 'browse', '1');
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

            if ($page > $bids_pages && $page > $items_pages) {   //user entered a big number ? go to last page
                $page = strval(max($bids_pages, $items_pages));
                $this->redirect('browse', 'page', $page);
            }

            (new View("browse"))->show([
                "page" => $page,
                "user" => $user,
                "my_bids" => $my_bids[min($page, $bids_pages) - 1] ?? [], //-1 for computer, if no items, empty array
                "available_items" => $my_available_items[min($page, $items_pages) - 1] ?? [],
                "my_bids_pagination" => $bids_pages,
                "available_items_pagination" => $items_pages,
            ]);
        }
    }

    public function show(): void {
        $member = $this->get_user_or_redirect();
        $errors = [];
        $success = "";

        $id = isset($_GET['param1']) ? (int)$_GET['param1'] : null;
        if (!$id) $this->redirect("item", "browse");

        $item_status = ItemStatus::get_item_status($id);
        if (!$item_status) $this->redirect("item","browse");

        $item = $item_status->get_item();

        $currentBid = $item_status->get_max_bid();
        if ($currentBid === null) $currentBid = $item->get_starting_bid();

        $bids = Bid::get_bids_for_item($item->get_id());

        $pictures = $item->get_pictures();
        $mainPic = $pictures[0] ?? null;

        // If a second parameter exists (clean URL image), override mainPic
        if (!empty($_GET['param2']) && !empty($pictures)) {
            $imgName = urldecode($_GET['param2']);
            foreach ($pictures as $pic) {
                $picName = pathinfo($pic->get_picture_path(), PATHINFO_BASENAME);
                if ($picName === $imgName) {
                    $mainPic = $pic;
                    break;
                }
            }
        }

        if (isset($_POST["bid"])) {
            $bidAmount = isset($_POST['make_bid']) ? (float)$_POST['make_bid'] : null;
            $errors = Bid::validate_bid($member, $item_status, $bidAmount);

            if (empty($errors)) {
                Bid::place_bid($member, $item_status, $bidAmount);
                $success = "You bidded successfully!";
            }
        }

        if (isset($_POST["buy"])) {
            $buyNowPrice = $item->get_buy_now_price() ?? 0;
            $currentBid = $item_status->get_max_bid() ?? $item->get_starting_bid();

            $errors = [];

            if (!$item_status->get_has_buy_now() || $item_status->get_buy_now_reached() || $buyNowPrice <= $currentBid) {
                $errors[] = "Cannot buy this item now.";
            } else {
                Bid::place_bid($member, $item_status, $buyNowPrice);
                $success = "You bought the item successfully!";
            }
        }

        // Redirect if POST and no errors (PRG)
        if (!empty($_POST) && empty($errors)) {
            $this->redirect("item", "show", $item->get_id());
        }


        (new View("item"))->show([
            "member" => $member,
            "item" => $item,
            "item_status" => $item_status,
            "currentBid" => $currentBid,
            "bids" => $bids,
            "mainPic" => $mainPic,
            "pictures"=>$pictures,
            "errors" => $errors,
            "success" => $success
        ]);
    }

    public function add(): void
    {
        $member = $this->get_user_or_redirect_guess();
        $item = null;
        $errors = [];

        // If form submitted
        if (!empty($_POST)) {
            // Fetch data
            $title = html_entity_decode($_POST['title'], ENT_QUOTES, 'UTF-8') ?? '';
            $description = html_entity_decode($_POST['description'], ENT_QUOTES, 'UTF-8') ?? null;
            $duration_days = isset($_POST['days']) ? (int)$_POST['days'] : 7;

            $starting_bid = isset($_POST['starting_bid']) && $_POST['starting_bid'] !== ''
                ? (float)$_POST['starting_bid']
                : null;

            $buy_now_price = isset($_POST['buy_now_price']) && $_POST['buy_now_price'] !== ''
                ? (float)$_POST['buy_now_price']
                : null;

            $sale_price = isset($_POST['sale_price']) && $_POST['sale_price'] !== ''
                ? (float)$_POST['sale_price']
                : null;

            // Si sale_price existe et reste est null, alors pas de bid et buy_now_price = $sale_price
            if ($sale_price !== null && $starting_bid === null && $buy_now_price === null) {
                $buy_now_price = $sale_price;
                $starting_bid = null;
            }

            $item = new Item(
                null,
                $title,
                $member,            // owner
                AppTime::get_current_datetime(), // created_at
                $duration_days,
                [],                 // pictures
                $description,
                $buy_now_price,
                $starting_bid
            );

            // Validate
            $errors = array_merge(
                $item->validate_title(),
                $item->validate_bid(),
                $item->validate_ipp(),
                $item->unicity()
            );

            // If valid, persist and redirect
            if (empty($errors)) {
                $item->persist();
                $this->redirect("item", "show", $item->get_id());
            }
        }

        // Show form
        (new View("add_edit_item"))->show([
            "member" => $member,
            "item" => $item,
            "errors" => $errors
        ]);
    }

    public function edit(): void
    {
        $member = $this->get_user_or_redirect_guess();
        $errors = [];

        // Param1 existe tjrs
        $id = (int)$_GET['param1'];

        // On fetch l'ID de la DB
        $item = Item::get_item($id);
        // Si l'ID est mauvais (existe pas)-> redirect
        if (!$item) {
            $this->redirect("item/browse");
        }
        // Si c'est pas le propriétaire qui modif -> redirect
        if ($item->get_owner()->get_id() !== $member->get_id()) {
            $this->redirect("item/browse");
        }

        // If form submitted
        if (!empty($_POST)) {
            // Fetch data
            $title = html_entity_decode($_POST['title'], ENT_QUOTES, 'UTF-8') ?? '';
            $description = html_entity_decode($_POST['description'], ENT_QUOTES, 'UTF-8') ?? null;
            $duration_days = isset($_POST['days']) ? (int)$_POST['days'] : 7;

            $starting_bid = isset($_POST['starting_bid']) && $_POST['starting_bid'] !== ''
                ? (float)$_POST['starting_bid']
                : null;

            $buy_now_price = isset($_POST['buy_now_price']) && $_POST['buy_now_price'] !== ''
                ? (float)$_POST['buy_now_price']
                : null;

            $sale_price = isset($_POST['sale_price']) && $_POST['sale_price'] !== ''
                ? (float)$_POST['sale_price']
                : null;

            // Si sale_price existe et reste est null, alors pas de bid et buy_now_price = $sale_price
            if ($sale_price !== null && $starting_bid === null && $buy_now_price === null) {
                $buy_now_price = $sale_price;
                $starting_bid = null;
            }

            // set the modified values
            $item->set_title($title);
            $item->set_description($description);
            $item->set_duration_days($duration_days);
            $item->set_starting_bid($starting_bid);
            $item->set_buy_now_price($buy_now_price);

            // Validate
            $errors = array_merge(
                $item->validate_title(),
                $item->validate_bid(),
                $item->validate_ipp(),
                $item->unicity()
            );

            // If valid, persist and redirect
            if (empty($errors)) {
                $item->persist();
                $this->redirect("item", "show", $item->get_id());
            }
        }

        // Show form
        (new View("add_edit_item"))->show([
            "member" => $member,
            "item" => $item,
            "errors" => $errors
        ]);

    }


    public function manage_images(): void {
        $user = $this->get_user_or_redirect_guess();
        $item_id = $this->get_id_or_redirect($_GET['param1']);
        $item = $this->get_item_or_redirect($item_id, $user);

        (new View("manage_images"))->show(["user" => $user, "item" => $item]);
    }

    public function add_images(): void {
        /* 
        doc: https://www.w3schools.com/TAGs/att_form_enctype.asp <-- important !
        doc: https://www.php.net/manual/en/features.file-upload.post-method.php <-- important !
        doc: https://www.php.net/manual/en/features.file-upload.errors.php
        doc: https://www.php.net/manual/en/function.pathinfo.php
        doc: https://www.php.net/manual/en/function.file-exists.php
        doc: https://www.php.net/manual/en/features.file-upload.php <-- see Jan's explanation for images too large !!!
        */
        $user = $this->get_user_or_redirect_guess();
        $item_id = $this->get_id_or_redirect($_GET['param1']);
        $item = $this->get_item_or_redirect($item_id, $user);
        $errors = [];

        if (isset($_POST['upload']) && isset($_FILES['images']) && !empty($_FILES['images']['type'][0])) {
            $files = $_FILES['images'];
            $errors = ItemPicture::validate_images($files);

            if (empty($errors)) {
                $items_dir = "uploads/items/" . $item->get_id();
                if (!is_dir($items_dir))
                    // Permission : owner peut r-w-x, group peut r-x, reste peut r-x
                    mkdir($items_dir, 0755, true);

                $priority = $item->get_next_priority();
                $images = [];

                foreach ($files['tmp_name'] as $tmp_name) {
                    //IMAGE
                    $image_filename = pathinfo($tmp_name, PATHINFO_FILENAME);
                    $final_image_path = $items_dir . "/" . $image_filename . ".jpg"; // "dir1/dir2" . "/" . "img.jpg"
                    $this->append_count_if_it_already_exist($final_image_path);
                    //THUMBNAIL
                    $thumb_filename = pathinfo($final_image_path, PATHINFO_FILENAME);
                    $final_thumb_path = $items_dir . "/" . $thumb_filename . "_thumbnail.jpg";

                    Image::resizeToJpeg($tmp_name, $final_image_path, Configuration::get("IMAGE_MAX_WIDTH"), Configuration::get("IMAGE_MAX_HEIGHT"), Configuration::get("JPEG_QUALITY"));
                    Image::resizeToJpeg($tmp_name, $final_thumb_path, Configuration::get("THUMB_MAX_WIDTH"), Configuration::get("THUMB_MAX_HEIGHT"), Configuration::get("JPEG_QUALITY"));

                    $pic = new ItemPicture($item, $priority, $final_image_path);
                    $item->add_picture($pic);

                    $priority++;
                }

                $item->persist_item_pictures();
                $this->redirect('item', 'manage_images', strval($item_id));
            }
        }
        (new View('manage_images'))->show(["user" => $user, "item" => $item, "errors" => $errors]);
    }

    public function remove_image(): void {
        $user = $this->get_user_or_redirect_guess();
        $item_id = $this->get_id_or_redirect($_GET['param1']);
        $picture_priority = $this->get_id_or_redirect($_POST['priority'], 'item', 'manage_images', $item_id);
        $item = $this->get_item_or_redirect($item_id, $user);
        $item_picture = $this->get_item_picture_or_redirect($item, $picture_priority);

        if ($item_picture->is_owner($user))
            $item_picture->delete();
        $this->redirect("item", "manage_images", strval($item_id));
    }

    public function move_image(): void {
        /*
        doc: https://beerpla.net/2009/02/17/swapping-column-values-in-mysql/
        doc: https://stackoverflow.com/questions/5029409/how-to-check-if-an-integer-is-within-a-range <-- sometimes simplicity is "ugly", but faster
        */
        $user = $this->get_user_or_redirect_guess();
        $item_id = $this->get_id_or_redirect($_GET['param1']);
        $priorities = isset($_POST['priority'])
            ? explode(",", $_POST['priority'])
            : [false, false];
        $picture_priority = $this->get_id_or_redirect($priorities[0], 'item', 'manage_images', $item_id);
        $target_priority = $this->get_id_or_redirect($priorities[1] ?? false, 'item', 'manage_images', $item_id);
        $item = $this->get_item_or_redirect($item_id, $user);
        $item_picture = $this->get_item_picture_or_redirect($item, $picture_priority);
        $target_item_picture = $this->get_item_picture_or_redirect($item, $target_priority);

        $item_picture->swap_priority($target_item_picture);
        $this->redirect("item", "manage_images", strval($item_id));
    }

    private function get_user_or_redirect_guess(bool $guest_pass = false): Member {
        /** @var Member $user */
        $user = $this->get_user_or_redirect();
        if ($user->is_guest() && !$guest_pass)
            $this->redirect("item", "browse");
        return $user;
    }

    private function get_id_or_redirect(mixed $id, string $controller = 'item', string $action = 'browse', string $param1 = '1'): int {
        if (!isset($id) || !NumberConverter::is_id($id))
            $this->redirect($controller, $action, $param1);
        $id = NumberConverter::get_id_or_false($id);
        return $id;
    }

    private function get_item_or_redirect(int $item_id, ?Member $user = null): Item {
        $item = Item::get_item($item_id);
        if (!$item || $user !== null && !$item->is_owner($user))
            $this->redirect("item", "browse", "1");
        return $item;
    }

    private function get_item_picture_or_redirect(Item $item, int $pic_priority): ItemPicture {
        $pic = $item->get_picture($pic_priority);
        if (!$pic)
            $this->redirect("item", "manage_images", strval($item->get_id()));
        return $pic;
    }

    private function append_count_if_it_already_exist(string &$path): void {
        $pathinfo = pathinfo($path);
        $temp_path = $pathinfo['dirname'] . "/" . $pathinfo['filename'] . ".jpg";

        if (file_exists($temp_path)) {
            $i = 1;
            do {
                $temp_path = $pathinfo['dirname'] . "/" . $pathinfo['filename'] . "_" . $i . ".jpg";
                $i++;
            } while (file_exists($temp_path));
        }

        $path = $temp_path;
    }
}
