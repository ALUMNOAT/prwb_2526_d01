<?php
/*
Categories ressemble structurellement à item_pictures
on a : 
- un id (pas la meme chose)
- un nom (un peu comme le path)
- une priorité (la meme chose)
*/

require_once "framework/Model.php";

class Categories extends Model
{
    public function __construct(
        private ?int $id,
        private string $name,
        private int $priority
    ) {
    }
    public function get_id(): ?int
    {
        return $this->id;
    }
    public function get_name(): string
    {
        return $this->name;
    }
    public function get_priority(): int
    {
        return $this->priority;
    }
    public static function get_one(int $id): ?Categories
    {
        $query = self::execute(
            "SELECT * 
            FROM categories 
            WHERE id = :id", 
            ["id" => $id]
        );
        $data = $query->fetch();
        return $data ? new Categories($data['id'], $data['name'], $data['priority']) : null;
    }
    public static function get_by_priority(int $priority): ?Categories
    {
        $query = self::execute(
            "SELECT * 
            FROM categories 
            WHERE priority = :priority", 
            ["priority" => $priority]
        );
        $data = $query->fetch();
        return $data ? new Categories($data['id'], $data['name'], $data['priority']) : null;
    }
    public static function get_max_priority(): int
    {
        $query = self::execute(
            "SELECT MAX(priority) as max_p 
            FROM categories", 
            []
        );
        $data = $query->fetch();
        return (int) ($data['max_p'] ?? 0);
    }
    public function set_id(?int $new_id): void
    {
        $this->id = $new_id;
    }
    public function set_name(string $new_name): void
    {
        $this->name = $new_name;
    }
    public function set_priority(int $new_priority): void
    {
        $this->priority = $new_priority;
    }
    public static function get_all_categories(): array
    {
        $query = self::execute(
            "SELECT *
            FROM categories",
            []
        );

        $data = $query->fetchAll();
        $results = [];
        foreach ($data as $row) {
            $results[] = new Categories(
                $row["id"],
                $row["name"],
                $row["priority"]
            );
        }
        return $results;
    }
    public static function get_all_categories_sorted_priority(): array
{
    $query = self::execute(
        "SELECT *
        FROM categories
        ORDER BY priority ASC",
        []
    );

    $data = $query->fetchAll();
    $results = [];
    foreach ($data as $row) {
        $results[] = new Categories(
            $row["id"],
            $row["name"],
            $row["priority"]
        );
    }
    return $results;
}
    // récupération de la méthode du member (persist)
    public function persist(): Categories
    {
        if ($this->id !== null) {
            self::execute(
                "UPDATE categories SET name=:name, priority=:priority WHERE id=:id",
                ["id" => $this->id, "name" => $this->name, "priority" => $this->priority]
            );
        } else {
            if ($this->priority === 0 || $this->priority === 1000) { 
                // si on creer une nouvelle categorie, on donne valeur de base 0
                $this->priority = self::get_max_priority() + 1;
        }
            // Insertion nouvelle catégorie (pas d'ID)
            self::execute(
                "INSERT INTO categories(name, priority) 
                VALUES(:name, :priority)",
                ["name" => $this->name, "priority" => $this->priority]
            );
            // Récupère l'id auto-incrémenté généré par MySQL
            $this->id = self::lastInsertId();
        }
        return $this;
    }
    public function delete(): void
    {
        if ($this->id !== null) {
            self::execute(
                "DELETE FROM categories
                WHERE id = :id",
                ["id" => $this->id]
            );

            self::execute(
                "UPDATE categories 
                SET priority = priority - 1 
                WHERE priority > :priority",
                ["priority" => $this->priority]
            );
        }
    }

    public function howMany(): int
    {
        $query = self::execute(
            "SELECT COUNT(category) as total
            FROM item_categories
            WHERE category = :id",
            ["id" => $this->id]
        );

        $data = $query->fetch();
        return (int) $data['total'];
    }

    public function isUsed(): bool
    {
        return $this->howMany() > 0;
    }

    public function validate_category_unicity(): array
    {
        $errors = [];
        $name = $this->get_name();

        $query = self::execute(
            "SELECT name 
            FROM categories
            WHERE name = :name",
            [":name" => $name]
        );

        $data = $query->fetch();

        // Si $data est false (rien trouvé), on arrête tout et on renvoie une liste d'erreurs vide
        if (!$data) {
            return [];
        }

        if ($data['name'] === $name) {
            $errors["category_name"] = "Cette catégorie '$name' existe déjà.";
        }
        return $errors;
    }

}