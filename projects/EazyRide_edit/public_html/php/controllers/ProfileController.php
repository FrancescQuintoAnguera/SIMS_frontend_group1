<?php
require_once __DIR__ . '/../models/User.php';

class ProfileController {
    public static function getProfile($user_id) {
        return User::getProfile($user_id);
    }

    public static function updateProfile($user_id, $data) {
        return User::updateProfile($user_id, $data);
    }

    public static function getUserInfo($user_id) {
        return User::getUserInfo($user_id);
    }
}