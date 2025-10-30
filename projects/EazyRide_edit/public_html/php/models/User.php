<?php
require_once __DIR__ . '/../core/DatabaseMariaDB.php';

class User {
    // Login
    public static function findByUsername($username) {
        $db = DatabaseMariaDB::getConnection();
        $stmt = $db->prepare("SELECT id, username, password, is_admin FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Register
    public static function findByUsernameOrEmail($username, $email) {
        $db = DatabaseMariaDB::getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = DatabaseMariaDB::getConnection();
        $stmt = $db->prepare("INSERT INTO users 
            (username, nationality_id, phone, birth_date, sex, dni, address, email, password, iban, driver_license_photo, minute_balance, is_admin, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )");

        $username = $data['username'] ?? null;
        $nationality_id = $data['nationality_id'] ?? null;
        $phone = $data['phone'] ?? null;
        $birth_date = $data['fecha_nacimiento'] ?? null;
        $sex = $data['sex'] ?? null;
        $dni = $data['dni'] ?? null;
        $address = $data['address'] ?? null;
        $iban = $data['iban'] ?? null;
        $email = $data['email'] ?? null;
        $driver_license_photo = $data['driver_license_photo'] ?? null;
        $minute_balance = 0;
        $is_admin = 0;
        $created_at = date('Y-m-d H:i:s');
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

        return $stmt->execute([
            $username,
            $nationality_id,
            $phone,
            $birth_date,
            $sex,
            $dni,
            $address,
            $email,
            $password_hash,
            $iban,
            $driver_license_photo,
            $minute_balance,
            $is_admin,
            $created_at
        ]);
    }

    // Profile
    public static function getProfile($user_id) {
        $db = DatabaseMariaDB::getConnection();
        $stmt = $db->prepare("SELECT fullname, dni, phone, birth_date AS birthdate, address, sex FROM users WHERE id = ?");

        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function updateProfile($user_id, $data) {
    $db = DatabaseMariaDB::getConnection();
    $stmt = $db->prepare("UPDATE users SET fullname = ?, dni = ?, phone = ?, birth_date = ?, address = ?, sex = ? WHERE id = ?");

    // Normalize the fields
    $birthdate = !empty($data['birthdate']) ? $data['birthdate'] : null;
    
// Normalize the value of sex to 'M', 'F', 'O', or NULL if it's empty or invalid

    $sex = isset($data['sex']) ? strtoupper($data['sex']) : null;
    if (!in_array($sex, ['M', 'F', 'O'])) {
        $sex = null; // prevents truncation errors
    }

    return $stmt->execute([
        $data['fullname'],
        $data['dni'],
        $data['phone'],
        $birthdate,
        $data['address'],
        $sex,
        $user_id
    ]);
}


    // Management
    public static function getUserInfo($user_id) {
        $db = DatabaseMariaDB::getConnection();
        $stmt = $db->prepare("SELECT username, email, minute_balance, is_admin FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}