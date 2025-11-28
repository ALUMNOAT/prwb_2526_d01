<?php
require_once "framework/Controller.php";

class ControllerBrowseitems extends Controller {
    public function index(): void {
        echo "<h1>This is the browsing page !</h1>";
        echo "<p><a href='index.php'>return to index</a><p>";
    }
}