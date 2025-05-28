<?php

namespace Controllers;

use PDO;
use PDOException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AthletesController
{
    private $db;
    private $tablename = 'athlete';

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
                array_walk($row, function (&$value) {
                    if ($value === null) {
                        $value = "";
                    }
                });
                $formatted[$id] = $row;
            }
        }

        return json_encode($formatted);
    }

    public function fields()
    {
        $stmt = $this->db->query("SELECT * FROM sport");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $formatted = [];
        $row['type'] = 'text';
        $row['value'] = "";
        $row['name'] = 'biography';
        $row['desc'] = 'биография';
        $formatted[] = $row;
        $row = [];
        $row['type'] = 'text';
        $row['value'] = "";
        $row['name'] = 'name';
        $row['desc'] = 'Название';
        $formatted[] = $row;
        $row = [];
        $row['type'] = 'text';
        $row['value'] = "";
        $row['name'] = 'birthday';
        $row['desc'] = 'день рождения';
        $formatted[] = $row;
        $row = [];
        $row['type'] = 'select';
        $row['value'] = "";
        $row['name'] = 'sport_id';
        $row['desc'] = 'Виды спорта';
        $row['options'] = $results;
        $formatted[] = $row;
        $row = [];
        $row['type'] = 'image';
        $row['value'] = "";
        $row['name'] = 'image_url';
        $row['desc'] = 'Изображение';
        $formatted[] = $row;

        return json_encode($formatted);
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

        $stmt = $this->db->query("SELECT * FROM sport");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formatted = [];
        $row['type'] = 'text';
        $row['value'] = $result['id'];
        $row['name'] = 'id';
        $row['desc'] = 'ID';
        $formatted[] = $row;
        $row = [];
        $row['type'] = 'text';
        $row['value'] = $result['biography'];
        $row['name'] = 'biography';
        $row['desc'] = 'биография';
        $formatted[] = $row;
        $row = [];
        $row['type'] = 'text';
        $row['value'] = $result['name'];
        $row['name'] = 'name';
        $row['desc'] = 'ФИО';
        $formatted[] = $row;
        $row = [];
        $row['type'] = 'text';
        $row['value'] = $result['birthday'];
        $row['name'] = 'birthday';
        $row['desc'] = 'день рождения';
        $formatted[] = $row;
        $row = [];
        $row['type'] = 'select';
        $row['value'] = $result['sport_id'];
        $row['name'] = 'sport_id';
        $row['desc'] = 'Виды спорта';
        $row['options'] = $results;
        $formatted[] = $row;
        $row = [];
        $row['type'] = 'image';
        $row['value'] = $result['image_url'];
        $row['name'] = 'image_url';
        $row['desc'] = 'Изображение';
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
            (name, sport_id, biography, birthday, image_url) 
            VALUES (:name, :sport_id, :biography, :birthday, :image_url)
        ");

        try {
            $stmt->execute([
                ':name' => $data['name'],
                ':sport_id' => $data['sport_id'] ?? null,
                ':biography' => $data['biography'] ?? "",
                ':birthday' => $data['birthday'] ?? null,
                ':image_url' => $data['image_url'] ?? null,
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

        foreach (['name', 'sport_id', 'biography', 'birthday', 'image_url'] as $field) {
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
    public function filter($sportId = null, $name = null, $birthday = null)
    {
        // Базовый SQL-запрос
        $sql = "SELECT * FROM {$this->tablename} WHERE 1=1";
        $params = [];

        // Добавляем условия фильтрации, если параметры не NULL
        if ($sportId !== null) {
            $sql .= " AND sport_id = :sport_id";
            $params[':sport_id'] = $sportId;
        }

        if ($name !== null) {
            $sql .= " AND name LIKE :name";
            $params[':name'] = "%{$name}%"; // Частичное совпадение
        }

        if ($birthday !== null) {
            $sql .= " AND birthday = :birthday";
            $params[':birthday'] = $birthday;
        }

        // Подготовка и выполнение запроса
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formatted = [];
        foreach ($result as $row) {
            if (isset($row['id'])) {
                $id = $row['id'];
                unset($row['id']);
                array_walk($row, function (&$value) {
                    if ($value === null) {
                        $value = "";
                    }
                });
                $formatted[$id] = $row;
            }
        }

        return json_encode($formatted);
    }
}
