<?php

class ProductController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getProducts()
    {
        $categoryId = $_GET['category_id'] ?? null;

        if ($categoryId !== null) {
            if (!filter_var($categoryId, FILTER_VALIDATE_INT) || (int)$categoryId <= 0) {
                http_response_code(400);
                echo json_encode([
                    'error' => 'Invalid category_id'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        $sql = "
            SELECT 
                p.id,
                p.name,
                p.price,
                p.stock,
                p.category_id,
                c.name AS category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
        ";

        if ($categoryId !== null) {
            $sql .= " WHERE p.category_id = :category_id";
        }

        $stmt = $this->db->prepare($sql);

        if ($categoryId !== null) {
            $stmt->bindValue(':category_id', (int)$categoryId, PDO::PARAM_INT);
        }

        $stmt->execute();

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);

        echo json_encode($products, JSON_UNESCAPED_UNICODE);
    }

    public function getProductById($id)
    {
        if (!filter_var($id, FILTER_VALIDATE_INT) || (int)$id <= 0) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Invalid product id'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $sql = "
            SELECT
                p.id,
                p.name,
                p.price,
                p.stock,
                p.category_id,
                c.name AS category_name,
                p.created_at
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.id = :id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);

        $stmt->execute();

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            http_response_code(404);
            echo json_encode([
                'error' => 'Product not found'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        http_response_code(200);

        echo json_encode($product, JSON_UNESCAPED_UNICODE);
    }
}