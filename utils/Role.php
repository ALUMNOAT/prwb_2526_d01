<?php
/*
https://github.com/php/php-src/issues/18997
https://github.com/php/php-src/issues/1481
*/

enum Role: string {
    case ADMIN = "admin";
    case USER = "user";
}