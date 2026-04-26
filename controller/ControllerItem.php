<?php
require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/ItemStatus.php";
require_once "utils/NumberConverter.php";
require_once 'utils/Image.php';
require_once 'model/Bid.php';
require_once 'utils/UrlDataEncoderDecoder.php';

class ControllerItem extends Controller
{

    public function index(): void
    {
        $this->redirect();
    }

    public function browse(): void
    {
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
            $available_items = Item::get_all_available_items($user);
            $my_available_items = array_diff_key($available_items, $my_bids);   // set theory: 'A - B' on keys (here it's item id)

            //each chunk is 12 items: chunk 1 is page 1; chunk 2 is page 2; etc...
            $my_bids = array_chunk($my_bids, $page_content_size, true);
            $my_available_items = array_chunk($my_available_items, $page_content_size, true);

            $bids_pages = count($my_bids);  //each chunk is a page, so 5 chunks = 5 pages
            $items_pages = count($my_available_items);


            //user entered a big number ? go to last page
            $max_pages = max($bids_pages, $items_pages);
            if ($max_pages > 0 && $page > $max_pages) {
                $this->redirect('item', 'browse', strval($max_pages));
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

    public function show(): void
    {
        $user = $this->get_user_or_redirect();
        $item_id = $this->get_id_or_redirect($_GET['param1']);
        $main_pic_priority = $this->get_priority_or_redirect($_GET['param2'] ?? 1);

        $item_status = $this->get_item_status_or_redirect($item_id);
        $item = $item_status->get_item();

        $main_pic = null;
        $picture = $item->get_pictures();
        if (!empty($picture)) {
            $main_pic = $item->get_picture($main_pic_priority);
            // If specific priority doesn't exist, fall back to first picture
            if (!$main_pic) {
                $main_pic = reset($picture); // Get first picture
            }
        }

        $bids = Bid::get_bids_for_item($item_id);
        $current_bid = $item_status->get_max_bid() ?? $item->get_starting_bid();

        (new View("item"))->show([
            "member" => $user,
            "item" => $item,
            "item_status" => $item_status,
            "currentBid" => $current_bid,
            "bids" => $bids,
            "main_pic" => $main_pic,
            "pictures" => $picture,
            "errors" => [],
            "success" => "",
            "bid_path" => $this->bid_path($item, $main_pic),
            "buy_path" => $this->buy_path($item, $main_pic)
        ]);
    }

    public function bid(): void
    {
        $user = $this->get_user_or_redirect_guess();
        $item_id = $this->get_id_or_redirect($_GET['param1']);
        $main_pic_priority = $this->get_priority_or_false($_GET['param2']);
        $errors = [];

        $item_status = $this->get_item_status_or_redirect($item_id);
        $item = $item_status->get_item();

        $main_pic = $main_pic_priority
            ? $this->get_item_picture_or_default($item, $main_pic_priority, 'item', 'show')
            : null;
        $current_bid = $item_status->get_max_bid() ?? $item->get_starting_bid();

        if (!$item_status->is_biddable($user))
            $this->redirect('item', 'show', $item_id);

        if (isset($_POST["bid_amount"]) && NumberConverter::is_positive_float($_POST["bid_amount"])) {
            $bid_amount = NumberConverter::get_positive_float_or_false($_POST["bid_amount"]);
            $errors = Bid::validate_bid($user, $item_status, $bid_amount);

            if (empty($errors)) {
                Bid::place_bid($user, $item_status, $bid_amount);
                $this->redirect("item", "show", $item_id, $main_pic_priority);
            }
        }

        (new View("item"))->show([
            "member" => $user,
            "item" => $item,
            "item_status" => $item_status,
            "currentBid" => $current_bid,
            "bids" => Bid::get_bids_for_item($item_id),
            "main_pic" => $main_pic,
            "pictures" => $item->get_pictures(),
            "errors" => $errors,
            "bid_path" => $this->bid_path($item, $main_pic),
            "buy_path" => $this->buy_path($item, $main_pic)
        ]);
    }

    public function buy(): void
    {
        //user and IDs
        $user = $this->get_user_or_redirect_guess();
        $item_id = $this->get_id_or_redirect($_GET['param1']);
        $main_pic_priority = $this->get_priority_or_false($_GET['param2']);
        $errors = [];

        //item, picture and bid
        $item_status = $this->get_item_status_or_redirect($item_id);
        $item = $item_status->get_item();
        $main_pic = $main_pic_priority
            ? $this->get_item_picture_or_default($item, $main_pic_priority, 'item', 'show')
            : null;
        $current_bid = $item_status->get_max_bid() ?? $item->get_starting_bid();

        if (!$item_status->is_buyable($user))
            $this->redirect('item', 'show', $item_id);

        if (isset($_POST["buy"])) {
            $buy_now_price = $item->get_buy_now_price();

            if (empty($errors)) {
                Bid::place_bid($user, $item_status, $buy_now_price);
                $this->redirect("item", "show", $item_id, $main_pic_priority);
            }
        }

        (new View("item"))->show([
            "member" => $user,
            "item" => $item_status->get_item(),
            "item_status" => $item_status,
            "currentBid" => $current_bid,
            "bids" => Bid::get_bids_for_item($item_id),
            "main_pic" => $main_pic,
            "pictures" => $item->get_pictures(),
            "errors" => $errors,
            "bid_path" => $this->bid_path($item, $main_pic),
            "buy_path" => $this->buy_path($item, $main_pic),
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
            
            if ($sale_price !== null && $starting_bid === null && $buy_now_price === null) {
                $buy_now_price = $sale_price;
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
            if (empty($errors)) {
                $errors = array_merge(
                    $errors,
                    $item->validate_title(),
                    $item->validate_date(),
                    $item->validate_prices($sale_price !== null),
                    $item->validate_description(),
                    $item->validate_unicity()
                );
            }

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
        $item_status = $this->get_item_status_or_redirect($id, $member);   //not owner, no access

        // On fetch l'ID de la DB
        $item = Item::get_item($id);
        // Si l'ID est mauvais (existe pas)-> redirect
        if (!$item) {
            $this->redirect("item", "browse");
        }
        if ($item_status->get_has_bids() || $item_status->has_sold()) {
            $this->redirect("item", "show", $item->get_id());
        }

        // If form submitted
        if (!empty($_POST)) {
            // Fetch data
            $title = html_entity_decode($_POST['title'], ENT_QUOTES, 'UTF-8') ?? '';
            $description = html_entity_decode($_POST['description'], ENT_QUOTES, 'UTF-8') ?? '';
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
                
            if ($sale_price !== null && $starting_bid === null && $buy_now_price === null) {
                $buy_now_price = $sale_price;
            }

            // set the modified values
            $item->set_title($title);
            $item->set_description($description);
            $item->set_duration_days($duration_days);
            $item->set_starting_bid($starting_bid);
            $item->set_buy_now_price($buy_now_price);

            // Validate
            if (empty($errors)) {
                $errors = array_merge(
                    $errors,
                    $item->validate_title(),
                    $item->validate_description(),
                    $item->validate_date(),
                    $item->validate_prices($sale_price !== null),
                    $item->validate_unicity()
                );
            }

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

    public function manage_images(): void
    {
        $user = $this->get_user_or_redirect_guess();
        $item_id = $this->get_id_or_redirect($_GET['param1']);
        $item_status = $this->get_item_status_or_redirect($item_id, $user);

        if ($item_status->get_has_bids() || $item_status->has_sold()) {
            $this->redirect("item", "show", $item_id);
        }

        (new View("manage_images"))->show(["user" => $user, "item" => $item_status->get_item()]);
    }

    public function add_images(): void
    {
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
        $item_status = $this->get_item_status_or_redirect($item_id, $user);
        $item = $item_status->get_item();

        if ($item_status->get_has_bids() || $item_status->has_sold()) {
            $this->redirect("item", "show", $item_id);
        }

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

    public function remove_image(): void
    {
        $user = $this->get_user_or_redirect_guess();
        $item_id = $this->get_id_or_redirect($_GET['param1']);
        $picture_priority = $this->get_priority_or_redirect($_POST['delete'], 'item', 'manage_images', $item_id);
        $item_status = $this->get_item_status_or_redirect($item_id, $user);
        $item = $item_status->get_item();

        if ($item_status->get_has_bids() || $item_status->has_sold()) {
            $this->redirect("item", "show", $item_id);
        }

        $item_picture = $this->get_item_picture_or_redirect($item, $picture_priority);

        if ($item_picture->is_owner($user))
            $item_picture->delete();
        $this->redirect("item", "manage_images", strval($item_id));
    }

    public function move_image(): void
    {
        /*
        doc: https://beerpla.net/2009/02/17/swapping-column-values-in-mysql/
        doc: https://stackoverflow.com/questions/5029409/how-to-check-if-an-integer-is-within-a-range <-- sometimes simplicity is "ugly", but faster
        */

        $user = $this->get_user_or_redirect_guess();
        $item_id = $this->get_id_or_redirect($_GET['param1']);
        $source_priority = $this->get_priority_or_redirect($_GET['param2'], 'item', 'show', $item_id);
        $item = $this->get_item_or_redirect($item_id, $user);
        if (isset($_POST['move'])) {
            $target_priority = $this->get_priority_or_redirect($_POST['move'], 'item', 'show', $item_id);
            $item_picture = $this->get_item_picture_or_redirect($item, $source_priority);
            $target_item_picture = $this->get_item_picture_or_redirect($item, $target_priority);

            $item_picture->swap_priority($target_item_picture);
            $this->redirect('item', 'manage_images', strval($item_id));
        }
        (new View("manage_images"))->show(["user" => $user, "item" => $item]);
    }

    public function db_update_priorities_service() : void {
        //https://reqbin.com/code/php/0uwqq41y/json-to-array-php-example#:~:text=Converting%20JSON%20to%20PHP%20Array,JSON%20to%20a%20PHP%20array.
        //https://www.php.net/var_export
        //https://www.php.net/manual/en/function.gettype.php
        //https://stackoverflow.com/questions/3559542/more-concise-way-to-check-to-see-if-an-array-contains-only-numbers-integers
        //https://laracasts.com/discuss/channels/laravel/convert-string-array-to-array-of-ints
        $user = $this->get_user_or_error();
        if($user !== false) {

            $item_status = $this->get_item_status_or_error($_GET['param1'], $user);
            if($item_status !== false) {
                /** @var ItemStatus $item_status */
                $error = $item_status->validate_remove_image();

                if($error !== []) {
                    $this->errorResponse(403, $error["move_image"]);

                } else {

                    if(!isset($_POST['priorities_after'])) {
                        $this->errorResponse(400, "No priorities given"); // <-- this error is not in the model, hence it's here ?

                    } else {
                        $priorities_after = $_POST['priorities_after'];
                        $error = $item_status->validate_new_priorities($priorities_after);

                        if($error !== []) {
                            $this->errorResponse(400, $error["priorities"]);
                        } else {
                            $item = $item_status->get_item();
                            $priorities_after = array_map('intval', $priorities_after);
                            ItemPicture::db_update_priorities($item, $priorities_after);
                        }
                    }
                }
            }
        }
    }

    public function remove_image_service(): void {
        $user = $this->get_user_or_error();
        if($user !== false) {

            $item_status = $this->get_item_status_or_error($_GET['param1'], $user);
            if($item_status !== false) {
                $error = $item_status->validate_remove_image();

                if($error !== []) {
                    $this->errorResponse(403, $error["remove_image"]); // <-- error validation done in the model

                } else {
                    $item = $item_status->get_item();
                    $picture = $this->get_item_picture_or_error($_POST['picture_priority_to_delete'], $item);

                    if($picture !== false) {
                        $picture->delete();
                        $this->jsonResponse();
                    }
                }
            }
        }
    }

    public function get_images_service(): void {
        //https://stackoverflow.com/questions/17576065/php-how-to-pass-parameter-which-may-be-undefined
        $user = $this->get_user_or_error();
        if($user !== false) {

            $id = $_GET['param1'] ?? null;
            $item_status = $this->get_item_status_or_error($id, $user, public_access: true);
            if($item_status !== false) {
                $item = $item_status->get_item();
                $pictures = $item->get_pictures();

                $pictures_path = ["id" => $id];
                foreach($pictures as $priority => $picture) {
                    $pictures_path["images"][] = ["priority" => $priority, "path" => $picture->get_picture_path()];
                }

                $this->jsonResponse($pictures_path);
            }
        }
    }

    private function jsonResponse($data = null, $status = 200, $message = "Success"): void {
        //https://webreference.com/php/web-development/ajax-json/
        http_response_code($status);
        
        $response = [
            'message' => $message,
            'data' => $data,
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        //exit;
    }

    private function errorResponse($status = 400, $error = "Bad request"): void {
        //https://webreference.com/php/web-development/ajax-json/
        http_response_code($status);
        
        $response = [
            "error" => $error,
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        //exit;
    }

    private function get_user_or_error($guest_pass = true): Member|false {
        /** @var Member $user */
        $user = $this->get_user_or_false();

        if(!$user) {
            $this->errorResponse(401, "You need to be connected to access this page");
        } else if ($user->is_guest() && !$guest_pass) {
            $this->errorResponse(403, "Guests do not have access to this page");
            $user = false;
        }

        return $user;
    }

    private function get_positive_int_or_error(mixed $id): int|false {
        if (!isset($id)) {
            $this->errorResponse(400, "No parameter found");
            $id = false;
        } else {
            $id = NumberConverter::get_id_or_false($id);
            if(!$id) {
                $this->errorResponse(400, "Parameter needs to be a strictly positive integer");
            }
        }

        return $id;
    }

    private function get_item_or_error(mixed $id, Member $owner, bool $public_access = false): Item|false {
        $item = false;
        $item_id = $this->get_positive_int_or_error($id);

        if($item_id !== false) {
            $item = Item::get_item($item_id);
            if(!$item) {
                $this->errorResponse(404, "No item with id $item_id was found");
            } else if (!$item->is_owner($owner) && !$public_access) {
                $this->errorResponse(403, "You are not allowed access to this item");
                $item = false;
            }
        }

        return $item;
    }

    private function get_item_status_or_error(mixed $id, Member $owner, bool $public_access = false): ItemStatus|false {
        $item_status = false;
        $item_id = $this->get_positive_int_or_error($id);

        if($item_id !== false) {
            $item_status = ItemStatus::get_item_status($item_id);
            if(!$item_status) {
                $this->errorResponse(404, "No item with id $item_id was found");
            } else if (!$item_status->is_owner($owner) && !$public_access) {
                $this->errorResponse(403, "You are not allowed access to this item");
                $item_status = false;
            }
        }

        return $item_status;
    }

    private function get_item_picture_or_error(mixed $priority, Item $item): ItemPicture|false {
        $picture = false; //we could use 1 variable only but it might be less readable... sad :(
        $picture_priority = $this->get_positive_int_or_error($priority);

        if($picture_priority !== false) {
            $picture = $item->get_picture($picture_priority);
            if(!$picture) {
                $this->errorResponse(404, "No picture with priority $picture_priority was found in \"".$item->get_title()."\"");
            } 
        }

        return $picture;
    }

    public function validate_full_item_service(): void
    {
        $user = $this->get_user_or_false();
        $id = isset($_GET['param1']) ? (int)$_GET['param1'] : null;
        $errors = [];

        // On récupère tout
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $days = (int)($_POST['days'] ?? 7);
        $starting_bid = $_POST['starting_bid'] === '' ? null : (float)$_POST['starting_bid'];
        $buy_now_price = $_POST['buy_now_price'] === '' ? null : (float)$_POST['buy_now_price'];
        $sale_price = $_POST['sale_price'] === '' ? null : (float)$_POST['sale_price'];

        // On crée l'item
        $item = new Item(
            $id,
            $title,
            $user,
            AppTime::get_current_datetime(),
            $days,
            [],
            $description,
            $buy_now_price,
            $starting_bid
            );

        // On récolte toutes les erreurs
        $errors = array_merge(
            $errors,
            $item->validate_title(),
            $item->validate_description(),
            $item->validate_date(),
            $item->validate_prices($sale_price !== null),
            $item->validate_unicity()
        );

        /* // On renvoie le tableau complet
        echo json_encode(['errors' => $errors]); */
        
        // Assure-toi que rien n'a été "echo" avant cette ligne
        header('Content-Type: application/json');
        
        echo json_encode([
            'debug_input' => [
                'id_detecte' => $id,
                'starting_bid' => $starting_bid,
                'buy_now_price' => $buy_now_price,
                'sale_price' => $sale_price,
                'is_sale_price_null' => ($sale_price === null)
            ],
            'errors' => $errors
        ], JSON_PRETTY_PRINT);
        
        exit; // On arrête le script ici pour éviter que du HTML parasite ne s'ajoute
    }

    public function get_rules_service(): void {
        echo json_encode(
            [
                "sale_duration_min" => Configuration::get("sale_duration_min", 1),
                "sale_duration_default" => Configuration::get("sale_duration_default", 7),
                "sale_duration_max" => Configuration::get("sale_duration_max", 365),
                "title_min_length" => Configuration::get("title_min_length", 3),
                "title_max_length" => Configuration::get("title_max_length", 255),
                "description_min_length" => Configuration::get("description_min_length", 3),
                "description_max_length" => Configuration::get("description_max_length", 10000)
            ],
            JSON_PRETTY_PRINT
        );
    }

    public function url_safe_encode_service(): void {
        $temp2 = $_POST['search'];
        $test = UrlDataEncoderDecoder::url_safe_encode($temp2);
        echo json_encode(["response" => $test]);
    }

    public function url_safe_decode_service(): void {
        $encoded_data = $_GET['param1'];
        $decoded_data = UrlDataEncoderDecoder::url_safe_decode($encoded_data);

        $answer = explode("|", $decoded_data, 2);
        $answer = ["field" => $answer[0], "search_value" => $answer[1]];        

        echo json_encode(["response" => $answer]);
    }

    private function get_user_or_redirect_guess(bool $guest_pass = false): Member
    {
        /** @var Member $user */
        $user = $this->get_user_or_redirect();
        if ($user->is_guest() && !$guest_pass)
            $this->redirect('item', 'browse');
        return $user;
    }

    private function get_id_or_redirect(mixed $id, string $controller = 'item', string $action = 'browse', string $param1 = '1'): int
    {
        if (!isset($id) || !NumberConverter::is_id($id))
            $this->redirect($controller, $action, $param1);
        $id = NumberConverter::get_id_or_false($id);
        return $id;
    }

    private function get_priority_or_redirect(mixed $priority, string $controller = 'item', string $action = 'browse', string $param1 = '1'): int
    {
        if (!NumberConverter::is_id($priority))
            $this->redirect($controller, $action, $param1);
        else
            $priority = NumberConverter::get_id_or_false($priority);

        return $priority;
    }

    private function get_priority_or_false(mixed $priority): int|false
    {
        if (!NumberConverter::is_id($priority))
            $priority = false;
        else
            $priority = NumberConverter::get_id_or_false($priority);

        return $priority;
    }

    private function get_item_or_redirect(int $item_id, ?Member $owner = null): Item
    {
        $item = Item::get_item($item_id);
        if (!$item || $owner !== null && !$item->is_owner($owner))
            $this->redirect("item", "browse", "1");
        return $item;
    }

    private function get_item_status_or_redirect(int $item_id, ?Member $owner = null): ItemStatus
    {
        $item_status = ItemStatus::get_item_status($item_id);
        if (!$item_status || $owner !== null && !$item_status->is_owner($owner))
            $this->redirect("item", "browse", "1");
        return $item_status;
    }

    private function get_item_picture_or_redirect(Item $item, int $pic_priority, string $controller = 'item', string $action = 'manage_images'): ItemPicture
    {
        $pic = $item->get_picture($pic_priority);
        if (!$pic)
            $this->redirect($controller, $action, strval($item->get_id()));
        return $pic;
    }

    private function get_item_picture_or_false(Item $item, int $pic_priority, string $controller = 'item', string $action = 'manage_images'): ItemPicture|false {
        $pic = $item->get_picture($pic_priority);
        if (!$pic)
            $pic = false;
        return $pic;
    }

    private function get_item_picture_or_default(Item $item, int $pic_priority, string $controller = 'item', string $action = 'manage_images'): ?ItemPicture
    {
        $pic = $item->get_picture($pic_priority);
        if (!$pic && $item->has_no_picture())
            $pic = null;
        else
            $pic = $item->get_picture(1);
        return $pic;
    }

    private function append_count_if_it_already_exist(string &$path): void
    {
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

    private function bid_path(Item $item, ?ItemPicture $main_pic): string
    {
        $bid_path = 'item/bid/' . $item->get_id();
        if (!is_null($main_pic))
            $bid_path = $bid_path . "/" . $main_pic->get_priority();
        return $bid_path;
    }

    private function buy_path(Item $item, ?ItemPicture $main_pic): string
    {
        $buy_path = 'item/buy/' . $item->get_id();
        if (!is_null($main_pic))
            $buy_path = $buy_path . "/" . $main_pic->get_priority();
        return $buy_path;
    }
}
