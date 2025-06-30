<?php
class Category {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllCategories() {
        $stmt = $this->pdo->query('SELECT * FROM categories ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE category_id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createCategory($name) {
        $stmt = $this->pdo->prepare('INSERT INTO categories (name) VALUES (:name)');
        return $stmt->execute(['name' => $name]);
    }

    public function updateCategory($id, $name) {
        $stmt = $this->pdo->prepare('UPDATE categories SET name = :name WHERE category_id = :id');
        return $stmt->execute([
            'id' => $id,
            'name' => $name
        ]);
    }

    public function deleteCategory($id) {
        $stmt = $this->pdo->prepare('DELETE FROM categories WHERE category_id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function getCategoriesForComic($comicId) {
        $stmt = $this->pdo->prepare('
            SELECT c.* 
            FROM categories c
            JOIN comic_categories cc ON c.category_id = cc.category_id
            WHERE cc.comic_id = :comic_id
            ORDER BY c.name
        ');
        $stmt->execute(['comic_id' => $comicId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignCategoriesToComic($comicId, array $categoryIds) {
        try {
            // Remove existing categories
            $stmt = $this->pdo->prepare('DELETE FROM comic_categories WHERE comic_id = :comic_id');
            $stmt->execute(['comic_id' => $comicId]);
            
            // Add new categories
            if (!empty($categoryIds)) {
                $stmt = $this->pdo->prepare('INSERT INTO comic_categories (comic_id, category_id) VALUES (:comic_id, :category_id)');
                foreach ($categoryIds as $categoryId) {
                    $stmt->execute([
                        'comic_id' => $comicId,
                        'category_id' => $categoryId
                    ]);
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error assigning categories to comic: " . $e->getMessage());
            throw $e;
        }
    }

    public function searchCategories($query) {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE name LIKE :query ORDER BY name');
        $stmt->execute(['query' => "%$query%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryComicsCount($categoryId) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT COUNT(DISTINCT cc.comic_id) as count
                FROM comic_categories cc
                WHERE cc.category_id = :category_id
            ');
            $stmt->execute(['category_id' => $categoryId]);
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            error_log("Error getting category comics count: " . $e->getMessage());
            return 0;
        }
    }

    public function getCategoriesWithFilters($search = '', $sort = 'name_asc') {
        try {
            $params = [];
            $sql = "SELECT c.*,
                          (SELECT COUNT(DISTINCT cc.comic_id) 
                           FROM comic_categories cc 
                           WHERE cc.category_id = c.category_id) as comics_count
                   FROM categories c";

            // Add search condition if search term is provided
            if (!empty($search)) {
                $sql .= " WHERE c.name LIKE :search";
                $params['search'] = "%$search%";
            }

            // Add sorting
            switch ($sort) {
                case 'name_desc':
                    $sql .= " ORDER BY c.name DESC";
                    break;
                case 'comics_asc':
                    $sql .= " ORDER BY comics_count ASC, c.name ASC";
                    break;
                case 'comics_desc':
                    $sql .= " ORDER BY comics_count DESC, c.name ASC";
                    break;
                case 'name_asc':
                default:
                    $sql .= " ORDER BY c.name ASC";
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting filtered categories: " . $e->getMessage());
            return [];
        }
    }
} 