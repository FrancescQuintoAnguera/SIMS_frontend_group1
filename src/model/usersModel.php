<?php
require_once __DIR__ . '/../db.php'; // Include the central PDO connection

class UserModel {
    private $pdo;

    // Constructor: receives the PDO instance from db.php
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Get a user by ID
    public function getUserById($id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users 
            WHERE id = :id AND deleted_at IS NULL
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // returns array or false
    }

    // Get a user by email
    public function getUserByEmail($email) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users 
            WHERE email = :email AND deleted_at IS NULL
        ");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create a new user
    public function createUser($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (id_tenan, name, surname, dni, email, password, id_role)
            VALUES (:id_tenan, :name, :surname, :dni, :email, :password, :id_role)
            RETURNING id
        ");
        $stmt->execute([
            'id_tenan' => $data['id_tenan'],
            'name'     => $data['name'],
            'surname'  => $data['surname'] ?? null,
            'dni'      => $data['dni'],
            'email'    => $data['email'],
            'password' => $data['password'], // should already be hashed
            'id_role'  => $data['id_role']
        ]);
        return $stmt->fetchColumn(); // returns the new user ID
    }

    // Update an existing user
    public function updateUser($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE users SET
                name = :name,
                surname = :surname,
                dni = :dni,
                email = :email,
                password = :password,
                id_role = :id_role,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id AND deleted_at IS NULL
        ");
        return $stmt->execute([
            'id'       => $id,
            'name'     => $data['name'],
            'surname'  => $data['surname'] ?? null,
            'dni'      => $data['dni'],
            'email'    => $data['email'],
            'password' => $data['password'], // should already be hashed
            'id_role'  => $data['id_role']
        ]);
    }

    // Soft delete a user (mark as deleted)
    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET deleted_at = CURRENT_TIMESTAMP
            WHERE id = :id AND deleted_at IS NULL
        ");
        return $stmt->execute(['id' => $id]);
    }

    // Get all active users (for admin)
    public function getAllUsers() {
        $stmt = $this->pdo->query("
            SELECT * FROM users 
            WHERE deleted_at IS NULL 
            ORDER BY id
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Instantiate the model using the PDO from db.php
$userModel = new UserModel($pdo);
