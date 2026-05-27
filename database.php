<?php

class Database
{
    private $host = '127.0.0.1';
    private $db_name = 'softprim_test';
    private $username = 'root';
    private $password = '';

    public function connect()
    {
        try {

            $pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password
            );

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;

        } catch (PDOException $e) {

            http_response_code(500);

            echo json_encode([
                'error' => 'Database connection failed',
                'message' => $e->getMessage()
            ]);

            exit;
        }
    }
}