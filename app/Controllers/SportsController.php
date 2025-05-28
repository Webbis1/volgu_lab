<?php

namespace Controllers;

use PDO;
use PDOException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class SportsController
{
    private $db;
    private $tablename = 'sport';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->tablename}");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formatted = [];
        foreach ($results as $row) {
            if (isset($row['id'])) {
                $id = $row['id'];
                unset($row['id']);
                $formatted[$id] = $row;
            }
        }

        return json_encode($formatted);
    }

    public function fields()
    {
        $row['type'] = 'text';
        $row['value'] = '';
        $row['name'] = 'name';
        $row['desc'] = 'Название';
        return json_encode($row);
    }

    public function read(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->tablename} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            // TODO
            return json_encode(['error' => 'Вид спорта не найден'], 404);
        }

        $formatted = [];
        $row['type'] = 'text';
        $row['value'] = $result['id'];
        $row['name'] = 'id';
        $row['desc'] = 'ID';
        $formatted[] = $row;
        $row = [];
        $row['type'] = 'text';
        $row['value'] = $result['name'];
        $row['name'] = 'name';
        $row['desc'] = 'Название';
        $formatted[] = $row;

        return json_encode($formatted);
    }

    public function create(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // Валидация обязательных полей
        if (empty($data['name'])) {
            // TODO
            return json_encode(['error' => 'Название обязательно'], 400);
        }

        $stmt = $this->db->prepare("
            INSERT INTO {$this->tablename} 
            (name) 
            VALUES (:name)
        ");

        try {
            $stmt->execute([
                ':name' => $data['name'],
            ]);

            $newId = $this->db->lastInsertId();
            return json_encode(['id' => $newId, 'success' => true], 201);
        } catch (PDOException $e) {
            return json_encode([';jgf' => $e->getMessage()], 500);
        }
    }

    public function update(int $id, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $check = $this->db->prepare("SELECT id FROM {$this->tablename} WHERE id = :id");
        $check->execute([':id' => $id]);

        if (!$check->fetch()) {
            return json_encode(['error' => 'Вид спорта не найден'], 404);
        }

        $fields = [];
        $params = [':id' => $id];

        foreach (['name'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($fields)) {
            return json_encode(['error' => 'Нет данных для обновления'], 400);
        }

        $query = "UPDATE {$this->tablename} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute($params);
            return json_encode(['success' => true]);
        } catch (PDOException $e) {
            return json_encode(['error' => $e->getMessage()], 500);
        }
    }

    public function delete(int $id)
    {
        $check = $this->db->prepare("SELECT id FROM {$this->tablename} WHERE id = :id");
        $check->execute([':id' => $id]);

        if (!$check->fetch()) {
            return json_encode(['error' => 'Вид спорта не найден'], 404);
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->tablename} WHERE id = :id");
            $stmt->execute([':id' => $id]);

            return json_encode(['success' => true]);
        } catch (PDOException $e) {
            return json_encode(['error' => $e->getMessage()], 500);
        }
    }
}
