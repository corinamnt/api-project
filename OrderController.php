<?php

class OrderController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createOrder()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Invalid JSON body'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $productId = $data['product_id'] ?? null;
        $quantity = $data['quantity'] ?? null;
        $customerEmail = trim($data['customer_email'] ?? '');

        if (!filter_var($productId, FILTER_VALIDATE_INT) || (int)$productId <= 0) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Invalid product_id'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!filter_var($quantity, FILTER_VALIDATE_INT) || (int)$quantity <= 0) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Invalid quantity'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (
            !filter_var($customerEmail, FILTER_VALIDATE_EMAIL) ||
            strlen($customerEmail) > 150
        ) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Invalid customer_email'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $this->db->beginTransaction();

            $productStmt = $this->db->prepare(
                "SELECT * FROM products WHERE id = :id FOR UPDATE"
            );

            $productStmt->bindValue(':id', (int)$productId, PDO::PARAM_INT);
            $productStmt->execute();

            $product = $productStmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                $this->db->rollBack();
                http_response_code(404);
                echo json_encode([
                    'error' => 'Product not found'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ((int)$quantity > (int)$product['stock']) {
                $this->db->rollBack();
                http_response_code(400);
                echo json_encode([
                    'error' => 'Insufficient stock'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $total = (float)$product['price'] * (int)$quantity;

            $insertStmt = $this->db->prepare(
                "INSERT INTO orders (product_id, quantity, customer_email, total)
                 VALUES (:product_id, :quantity, :customer_email, :total)"
            );

            $insertStmt->execute([
                ':product_id' => $productId,
                ':quantity' => $quantity,
                ':customer_email' => $customerEmail,
                ':total' => $total
            ]);

            $orderId = $this->db->lastInsertId();

            $updateStmt = $this->db->prepare(
                "UPDATE products SET stock = stock - :quantity WHERE id = :id"
            );

            $updateStmt->execute([
                ':quantity' => $quantity,
                ':id' => $productId
            ]);

            $this->db->commit();

            $createdStmt = $this->db->prepare(
                "SELECT created_at FROM orders WHERE id = :id"
            );

            $createdStmt->execute([
                ':id' => $orderId
            ]);

            $createdAt = $createdStmt->fetchColumn();

            http_response_code(201);

            echo json_encode([
                'order_id' => (int)$orderId,
                'product_id' => (int)$productId,
                'quantity' => (int)$quantity,
                'total' => (float)$total,
                'created_at' => $createdAt
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            http_response_code(500);

            echo json_encode([
                'error' => 'Failed to create order',
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}