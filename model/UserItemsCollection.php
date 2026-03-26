<?php
require_once "framework/Model.php";
require_once "model/Member.php";

class UserItemsCollection extends Model {
    //ATTRIBUTES
    private array $items = [];

    //__CONSTRUCT
    public function __construct(
        private Member $user
    ) {
    }
}