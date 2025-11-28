<?php
require_once "framework/Controller.php";

class ControllerTest extends Controller {
    public function index(): void {
        echo "<h1>Hello PRWB_2526_XYY !</h1>";
        echo "<p><a href='time'>Time management</a><p>";
    }
}