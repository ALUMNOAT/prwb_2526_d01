<?php
require_once "framework/Controller.php";
require_once "model/Item.php";
require_once "model/ItemStatus.php";
require_once "utils/NumberConverter.php";
require_once 'utils/Image.php';


class ControllerItem extends Controller {

    public function index(): void {
        $this->redirect();
    }

    public function show(): void {
        $member = $this->get_user_or_redirect();

        // récupérer l'ID depuis GET
        $id = isset($_GET['param1']) ? (int)$_GET['param1'] : null;

        if (!$id) {
            // si pas d'ID -> rediriger vers browse
            $this->redirect("browse");
        }

        $item_status = ItemStatus::get_item_status((int)$id);

        if (!$item_status) {
            $this->redirect("browse");
        }

        (new View("item"))->show(["member" => $member, "item" => $item_status->get_item(), "item_status" => $item_status]);
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

                foreach ($files['error'] as $key => $error) {
                    $pathinfo = pathinfo($files['name'][$key]);
                    $final_image_path = $items_dir . "/" . $pathinfo['basename']; // "dir1/dir2" . "/" . "img.jpg"
                    $final_thumb_path = $final_image_path; //we add "_thumbnail" in the function that follows:
                    $this->append_count_if_it_already_exist($final_image_path);
                    $this->append_count_if_it_already_exist($final_thumb_path, "_thumbnail");

                    $temp_image_path = $files['tmp_name'][$key];
                    Image::resizeToJpeg($temp_image_path, $final_image_path, Configuration::get("IMAGE_MAX_WIDTH"), Configuration::get("IMAGE_MAX_HEIGHT"), Configuration::get("JPEG_QUALITY"));
                    Image::resizeToJpeg($temp_image_path, $final_thumb_path, Configuration::get("THUMB_MAX_WIDTH"), Configuration::get("THUMB_MAX_HEIGHT"), Configuration::get("JPEG_QUALITY"));

                    (new ItemPicture($item, $priority, $final_image_path))->persist();
                    $priority++;
                }

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
            $this->redirect('browse');
        return $user;
    }

    private function get_id_or_redirect(mixed $id, string $controller = 'browse', string $action = 'page', string $param1 = '1'): int {
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

    private function get_item_picture_or_redirect(Item $item, int $pic_priority): ItemPicture {
        $pic = $item->get_picture($pic_priority);
        if (!$pic)
            $this->redirect("item", "manage_images", strval($item->get_id()));
        return $pic;
    }

    private function append_count_if_it_already_exist(string &$path, string $append = ""): void {
        $pathinfo = pathinfo($path);

        if ($append != "")
            $path = $pathinfo['filename'] . $append . $pathinfo['extension'];

        if (file_exists($path)) {
            $temp_path = $path;
            $i = 1;
            do {
                $temp_path = $pathinfo['dirname'] . $pathinfo['filename'] . "_" . $i . $append . "." . $pathinfo['extension'];
                $i++;
            } while (file_exists($temp_path));

            $path = $temp_path;
        }
    }
}
