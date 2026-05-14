<?php
/*
Categories ressemble structurellement à item_pictures
on a : 
- un id (pas la meme chose)
- un nom (un peu comme le path)
- une priorité (la meme chose)
*/

require_once "framework/Model.php";

class Categories extends Model {
    public function __construct(
        private ?int $id,
        private string $name,
        private int $priority
    ) { }
    public function get_id(): ?int {
        return $this->id;
    }
    public function get_name(): string {
        return $this->name;
    }
    public function get_priority(): int {
        return $this->priority;
    }
    public function set_id(?int $new_id): void {
        $this->id = $new_id;
    }
    public function set_name(string $new_name): void {
        $this->name = $new_name;
    } 
    public function set_priority(int $new_priority): void {
        $this->priority = $new_priority;
    }
    public static function get_all_categories(): array {
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
    // récupération de la méthode du member (persist)
    public function persist(): Categories {
        // Si l'ID existe (categorie existe), on update
        if ($this->id !== null) {
            // On met à jour la catégorie existante (un ID attribué déjà)
            self::execute(
                "UPDATE categories
                SET name=:name, priority=:priority, 
                WHERE id=:id",
                ["id" => $this->id, 
                "name" => $this->name,
                "priority" => $this->priority]
            );
        } else {
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
    public function delete(): void {
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

}