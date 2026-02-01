<?php

class NumberConverter {
    /*
    doc: https://stackoverflow.com/questions/4844916/best-way-to-check-for-positive-integer-php
    */
    public static function get_page_or_false(mixed $page): int|false {
        return filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    }
    public static function get_id_or_false(mixed $id): int|false {
        return filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    }
    public static function get_positive_int_or_false(mixed $amount): int|false {
        return filter_var($amount, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    }
    public static function get_positive_float_or_false(mixed $amount): float|false {
        return filter_var($amount, FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);
    }

    public static function is_page(mixed $page): bool {
        return is_int(self::get_page_or_false($page));
    }
    public static function is_id(mixed $id): bool {
        return is_int(self::get_id_or_false($id));
    }
    public static function is_positive_int(mixed $int): bool {
        return is_int(self::get_positive_int_or_false($int));
    }
    public static function is_positive_float(mixed $float): bool {
        return is_float(self::get_positive_float_or_false($float));
    }

    public static function money_format(float|int $price): string {
        return number_format($price, 2, ",", ".");
    }
}
