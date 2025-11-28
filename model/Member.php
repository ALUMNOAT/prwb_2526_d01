<?php

require_once "framework/Model.php";
require_once "utils/Role.php";


public function __construct(
    private int $id, 
    private string $full_name, 
    private string $email,
    private string $pseudo,
    private string $password,
    private Role $role, 
    private ?string $picture_path = null, 
    private ?string $iban = null) {
}