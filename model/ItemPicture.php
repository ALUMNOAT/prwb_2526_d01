<?php
/*
doc: https://www.php.net/substr_replace

*/
require_once "framework/Model.php";
require_once "model/Item.php";


class ItemPicture extends Model {
    private ?ItemPicturesCollection $pictures_ = null;

    public function __construct(
        private Item $item,
        private int $priority,
        private string $picture_path,
        private ?string $thumbnail_path = null
    ) {
        if (file_exists($this->get_thumbnail_path()))
            $this->thumbnail_path = $this->get_thumbnail_path();
        $this->pictures_ = $item->get_pictures_();
    }

    public function get_item(): Item {
        return $this->item;
    }


    //NUMBERS
    public function get_priority(): int {
        return $this->priority;
    }


    //STRING
    public function get_picture_path(): string {
        return $this->picture_path;
    }
    public function get_thumbnail_path(): string {
        /*
        doc: https://stackoverflow.com/questions/4636166/only-variables-should-be-passed-by-reference
        */
        $decomposed_path = explode(".", $this->picture_path);
        $extension_size = strlen(end($decomposed_path)) + 1; //+1 to include the dot
        $thumbnail_path = substr_replace($this->picture_path, "_thumbnail", -$extension_size, -$extension_size);
        return $thumbnail_path;
    }

    public function set_item(Item $new_item): void {
        $this->item = $new_item;
    }
    public function set_priority(int $new_priority): void {
        $this->priority = $new_priority;
    }
    public function set_picture_path(string $new_picture_path): void {
        $this->picture_path = $new_picture_path;
    }

    public function is_owner(Member $user): bool {
        return $this->item->is_owner($user);
    }
    public function is_first_priority(): bool {
        return $this->pictures_->is_first_priority($this);
    }
    public function is_last_priority(): bool {
        return $this->pictures_->is_last_priority($this);
    }
    public static function get_main_picture(Item $item): ItemPicture|false {
        $query = self::execute(
            "SELECT * FROM item_pictures
            WHERE item = :id
            AND priority = 1", //simplicity at its best 
            ["id" => $item->get_id()]
        );

        $item_picture = false;

        if ($query->rowCount() > 0) {
            $row = $query->fetch();
            $item_picture = ItemPicture::get_item_picture_from_row($row, $item);
        }
        
        return $item_picture;
        
    }
    public static function get_items_picture(array $items): array {
        /*
        doc: https://www.php.net/manual/en/function.array-keys.php
        doc: https://www.daniweb.com/programming/web-development/threads/266686/how-to-use-an-php-array-in-an-sql-query
        */
        if (empty($items))
            return [];

        $placeholders = [];
        $params = [];
        foreach ($items as $i => $u) {
            $placeholders[] = ":u$i";
            $params[":u$i"] = $u->get_id();
        }

        $query = self::execute(
            "SELECT * 
            FROM item_pictures
            WHERE item IN ("
                . implode(',', $placeholders) .
                ")",
            $params
        );

        $data = $query->fetchAll();
        $items_pictures = [];
        foreach ($data as $row) {
            $item = $items[$row['item']];
            $item_picture = ItemPicture::get_item_picture_from_row($row, $item);
            $items_pictures[$row['item']][$row['priority']] = $item_picture;
        }

        return $items_pictures;
    }
    public static function get_item_picture(Item $item, int $priority): ItemPicture|false {
        $query = self::execute(
            "SELECT picture_path
            FROM item_pictures
            WHERE item = :item_id
            AND priority = :priority;
            ",
            ["item_id" => $item->get_id(), "priority" => $priority]
        );

        $item_picture = false;

        if ($query->rowCount() > 0) {
            $row = $query->fetch();
            $item_picture = ItemPicture::get_item_picture_from_row($row, $item);
        }

        return $item_picture;
    }
    public static function get_item_picture_from_row(array $row, Item $item, String $priority = "priority", String $picture_path = "picture_path"): ItemPicture {
        return new ItemPicture($item, $row[$priority], $row[$picture_path]);
    }

    public function persist(): void {
        self::execute(
            "INSERT INTO item_pictures(item,priority,picture_path) 
            VALUES(:item_id, :priority, :picture_path)",
            [
                "item_id" => $this->item->get_id(),
                "priority" => $this->priority,
                "picture_path" => $this->picture_path
            ]
        );
    }
    public function delete(): void {
        /*
        doc: https://stackoverflow.com/questions/9407963/declaring-sql-variables-sql-server
        doc: https://stackoverflow.com/questions/17999360/sql-server-increment-int-column-using-variable
        doc: https://stackoverflow.com/questions/11754781/how-to-declare-a-variable-in-mysql
        doc: https://stackoverflow.com/questions/9631096/mysql-incrementing-value
        doc: https://gist.github.com/irazasyed/4253470
        */
        $query = self::execute(
            "DELETE FROM item_pictures
            WHERE item = :item_id
            AND priority = :priority;

            SET @p := 0;                #reset priority for each delete !
            
            UPDATE item_pictures
            SET priority = @p := (@p + 1)
            WHERE item = :item_id;",
            [
                "item_id" => $this->item->get_id(),
                "priority" => $this->priority
            ]
        );
        if (file_exists($this->picture_path))
            unlink($this->picture_path);
        if (file_exists($this->thumbnail_path))
            unlink($this->thumbnail_path);
    }
    public function swap_priority(ItemPicture $other_pic): void {
        /*
        doc: https://stackoverflow.com/questions/3667439/setting-multiple-column-using-one-update
        doc: https://stackoverflow.com/questions/11207574/how-to-swap-values-of-two-rows-in-mysql-without-violating-unique-constraint
        */
        if ($this->get_priority() != $other_pic->get_priority()) {
            $query = self::execute(
                "UPDATE item_pictures 
                SET priority = -priority
                WHERE (item, priority) = (:item_id, :pic_priority);

                UPDATE item_pictures 
                SET priority = :pic_priority
                WHERE (item, priority) = (:item_id, :other_pic_priority);

                UPDATE item_pictures 
                SET priority = :other_pic_priority
                WHERE (item, priority) = (:item_id, -:pic_priority);

                SET @p := 0;
                
                UPDATE item_pictures
                SET priority = @p := (@p + 1)
                WHERE item = :item_id;",
                [
                    "item_id" => $this->item->get_id(),
                    "pic_priority" => $this->priority,
                    "other_pic_priority" => $other_pic->get_priority()
                ]
            );
        }
    }

    //VALIDATE
    public static function validate_images(array $file): array {
        /*
        doc: https://stackoverflow.com/questions/1957629/how-to-access-a-private-member-inside-a-static-function-in-php
        explanation: Files uploaded to PHP do not follow OOP, but instead is a list where each data is in its own array. 
        Therefore, the error_code of each file is in a single list, the name of each file is in another list, etc... weird but ok

        doc: https://www.php.net/manual/en/function.mime-content-type.php
        */
        $errors = [];

        foreach ($file["error"] as $key => $error_code) {
            $name = $file["name"][$key];
            if ($error_code !== UPLOAD_ERR_OK)
                $errors[$name][] = self::$phpFileUploadErrors[$error_code];

            if ($file['size'][$key] > Configuration::get("MAX_UPLOAD_SIZE"))
                $errors[$name][] = "Le fichier dépasse la taille maximale autorisée (5MB).";

            $mime = mime_content_type($file['tmp_name'][$key]);

            if (!in_array($mime, self::$valid_types, true))
                $errors[$name][] = "Format d'image non supporté.";
        }

        return $errors;
    }
}
