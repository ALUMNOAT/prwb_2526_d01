<?php
/*
doc: https://www.php.net/substr_replace

*/
require_once "framework/Model.php";
require_once "model/Item.php";


class ItemPicture extends Model {

    public function __construct(
        private Item $item,
        private int $priority,
        private string $picture_path,
        private ?string $thumbnail_path = null
    ) {
        if (file_exists($this->get_thumbnail_path()))
            $this->thumbnail_path = $this->get_thumbnail_path();
    }

    public function get_item(): Item {
        return $this->item;
    }
    public function get_priority(): int {
        return $this->priority;
    }
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

    public static function get_main_picture(Item $item): ItemPicture|false {
        $query = self::execute(
            "SELECT * FROM item_pictures
            WHERE item = :id
            AND priority = 1", //simplicity at its best 
            ["id" => $item->get_id()]
        );

        if ($query->rowCount() == 0)
            return false;
        else {
            $row = $query->fetch();
            $item_picture = new ItemPicture(
                $item, //should I use the variable $item or call the static method ?
                $row['priority'],
                $row['picture_path']
            );
            return $item_picture;
        }
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
            $items_pictures[$row['item']][] = new ItemPicture(
                $items[$row['item']],
                $row['priority'],
                $row['picture_path']
            );
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

        if ($query->rowCount() === 0)
            return false;

        else {
            $row = $query->fetch();
            $item_picture = new ItemPicture($item, $priority, $row['picture_path']);

            return $item_picture;
        }
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

    public static function validate_image(array $file): array {
        $errors = [];

        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors["critical error"] = "Une erreur est survenue lors de l'upload.";
            return $errors;
        }

        if ($file['size'] > Configuration::get("MAX_UPLOAD_SIZE")) {
            $errors["file size"] = "Le fichier dépasse la taille maximale autorisée (5MB).";
        }

        $mime = mime_content_type($file['tmp_name']);
        $valid_types = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp'
        ];

        // Si j'envoye un fichier .mp3, ceci s'affiche
        if (!in_array($mime, $valid_types, true)) {
            $errors["unsupported"] = "Format d'image non supporté.";
        }

        return $errors;
    }
}
