<?php
class Comic {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllComics($limit = null, $offset = 0) {
        $sql = 'SELECT c.*, GROUP_CONCAT(cat.name) as categories 
                FROM comics c 
                LEFT JOIN comic_categories cc ON c.comic_id = cc.comic_id 
                LEFT JOIN categories cat ON cc.category_id = cat.category_id 
                GROUP BY c.comic_id';
        
        $params = [];
        if ($limit !== null) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $params[':limit'] = (int)$limit;
            $params[':offset'] = (int)$offset;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getComicById($comicId) {
        $sql = 'SELECT c.*, GROUP_CONCAT(cat.name) as categories 
                FROM comics c 
                LEFT JOIN comic_categories cc ON c.comic_id = cc.comic_id 
                LEFT JOIN categories cat ON cc.category_id = cat.category_id 
                WHERE c.comic_id = :comic_id 
                GROUP BY c.comic_id';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['comic_id' => $comicId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function searchComics($query, $categories = []) {
        $sql = 'SELECT DISTINCT c.*, GROUP_CONCAT(cat.name) as categories 
                FROM comics c 
                LEFT JOIN comic_categories cc ON c.comic_id = cc.comic_id 
                LEFT JOIN categories cat ON cc.category_id = cat.category_id 
                WHERE c.title LIKE :query OR c.author LIKE :query';

        if (!empty($categories)) {
            $sql .= ' AND cat.name IN (' . str_repeat('?,', count($categories) - 1) . '?)';
        }

        $sql .= ' GROUP BY c.comic_id';
        
        $stmt = $this->pdo->prepare($sql);
        $params = ['query' => "%$query%"];
        
        if (!empty($categories)) {
            foreach ($categories as $i => $category) {
                $params[$i + 1] = $category;
            }
        }

        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function createComic($title, $author, $publisher, $price, $stock = 0, $imageData = null, $imageType = null, $imageName = null) {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO comics (title, author, publisher, price, stock, image_data, image_type, image_name) 
                                       VALUES (:title, :author, :publisher, :price, :stock, :image_data, :image_type, :image_name)');
            
            $success = $stmt->execute([
                'title' => $title,
                'author' => $author,
                'publisher' => $publisher,
                'price' => $price,
                'stock' => $stock,
                'image_data' => $imageData,
                'image_type' => $imageType,
                'image_name' => $imageName
            ]);

            if (!$success) {
                throw new Exception('Failed to create comic');
            }

            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function updateComic($comicId, $data) {
        try {
            // Validate comic exists
            $stmt = $this->pdo->prepare('SELECT comic_id FROM comics WHERE comic_id = :comic_id');
            $stmt->execute(['comic_id' => $comicId]);
            if (!$stmt->fetch()) {
                error_log("Error updating comic: Comic ID {$comicId} not found");
                return false;
            }

            // Map 'image' to 'image_name' if it exists
            if (isset($data['image'])) {
                $data['image_name'] = $data['image'];
                unset($data['image']);
            }

            $allowedFields = ['title', 'author', 'publisher', 'price', 'stock', 'description', 'image_name', 'image_type', 'image_data'];
            $updates = array_intersect_key($data, array_flip($allowedFields));
            
            if (empty($updates)) {
                error_log("Error updating comic: No valid fields to update");
                return false;
            }

            // Build update query
            $sql = 'UPDATE comics SET ' . 
                   implode(', ', array_map(fn($field) => "$field = :$field", array_keys($updates))) .
                   ' WHERE comic_id = :comic_id';
            
            $updates['comic_id'] = $comicId;

            // Log the query and parameters for debugging
            error_log("Update Comic Query: " . $sql);
            error_log("Update Comic Params: " . print_r($updates, true));

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($updates);

            if (!$result) {
                error_log("Error updating comic: " . implode(", ", $stmt->errorInfo()));
                return false;
            }

            return true;
        } catch (Exception $e) {
            error_log("Error updating comic: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateStock($comicId, $quantity) {
        $stmt = $this->pdo->prepare('UPDATE comics SET stock = stock + :quantity WHERE comic_id = :comic_id');
        return $stmt->execute([
            'comic_id' => $comicId,
            'quantity' => $quantity
        ]);
    }

    public function setCategories($comicId, array $categoryIds) {
        $this->pdo->beginTransaction();
        
        try {
            // Remove existing categories
            $stmt = $this->pdo->prepare('DELETE FROM comic_categories WHERE comic_id = :comic_id');
            $stmt->execute(['comic_id' => $comicId]);
            
            // Add new categories
            $stmt = $this->pdo->prepare('INSERT INTO comic_categories (comic_id, category_id) VALUES (:comic_id, :category_id)');
            foreach ($categoryIds as $categoryId) {
                $stmt->execute([
                    'comic_id' => $comicId,
                    'category_id' => $categoryId
                ]);
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    public function getCategories() {
        $stmt = $this->pdo->query('SELECT * FROM categories ORDER BY name');
        return $stmt->fetchAll();
    }

    public function getFeaturedComics($limit = 6) {
        $sql = 'SELECT c.*, GROUP_CONCAT(cat.name) as categories,
                       COALESCE(c.image_name, "default-comic.jpg") as image
                FROM comics c 
                LEFT JOIN comic_categories cc ON c.comic_id = cc.comic_id 
                LEFT JOIN categories cat ON cc.category_id = cat.category_id 
                WHERE c.stock > 0 
                GROUP BY c.comic_id 
                ORDER BY RAND() 
                LIMIT :limit';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':limit' => (int)$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLatestComics($limit = 6) {
        $sql = 'SELECT c.*, GROUP_CONCAT(cat.name) as categories,
                       COALESCE(c.image_name, "default-comic.jpg") as image
                FROM comics c 
                LEFT JOIN comic_categories cc ON c.comic_id = cc.comic_id 
                LEFT JOIN categories cat ON cc.category_id = cat.category_id 
                WHERE c.stock > 0 
                GROUP BY c.comic_id 
                ORDER BY c.comic_id DESC 
                LIMIT :limit';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':limit' => (int)$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setImage($comicId, $imageData, $imageType, $imageName) {
        $stmt = $this->pdo->prepare('UPDATE comics 
                                    SET image_data = :image_data, 
                                        image_type = :image_type, 
                                        image_name = :image_name 
                                    WHERE comic_id = :comic_id');
        
        return $stmt->execute([
            'comic_id' => $comicId,
            'image_data' => $imageData,
            'image_type' => $imageType,
            'image_name' => $imageName
        ]);
    }

    public function getImage($comicId) {
        $stmt = $this->pdo->prepare('SELECT image_data, image_type, image_name 
                                    FROM comics 
                                    WHERE comic_id = :comic_id');
        $stmt->execute(['comic_id' => $comicId]);
        return $stmt->fetch();
    }

    public function getComicsCount($filters = []) {
        $sql = 'SELECT COUNT(DISTINCT c.comic_id) as count 
                FROM comics c 
                LEFT JOIN comic_categories cc ON c.comic_id = cc.comic_id 
                LEFT JOIN categories cat ON cc.category_id = cat.category_id';

        $where = [];
        $params = [];

        // Only add category filter if it's not empty
        if (!empty($filters['category'])) {
            $where[] = 'cat.name = ?';
            $params[] = $filters['category'];
        }

        // Only add search filter if it's not empty
        if (!empty($filters['search'])) {
            $where[] = '(LOWER(c.title) LIKE LOWER(?) OR LOWER(c.author) LIKE LOWER(?))';
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Only add WHERE clause if there are conditions
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        try {
            error_log('getComicsCount SQL: ' . $sql);
            error_log('getComicsCount Params: ' . print_r($params, true));
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log('getComicsCount Result: ' . print_r($result, true));
            return $result ? (int)$result['count'] : 0;
        } catch (PDOException $e) {
            error_log('Error in getComicsCount: ' . $e->getMessage());
            return 0;
        }
    }

    public function getFilteredComics($filters = [], $sort = 'newest', $limit = null, $offset = 0) {
        $sql = 'SELECT DISTINCT c.*, GROUP_CONCAT(DISTINCT cat.name) as categories,
                       COALESCE(c.image_name, "default-comic.jpg") as image 
                FROM comics c 
                LEFT JOIN comic_categories cc ON c.comic_id = cc.comic_id 
                LEFT JOIN categories cat ON cc.category_id = cat.category_id';

        $where = [];
        $params = [];

        // Only add category filter if it's not empty
        if (!empty($filters['category'])) {
            $where[] = 'cat.name = ?';
            $params[] = $filters['category'];
        }

        // Only add search filter if it's not empty
        if (!empty($filters['search'])) {
            $where[] = '(LOWER(c.title) LIKE LOWER(?) OR LOWER(c.author) LIKE LOWER(?))';
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Only add WHERE clause if there are conditions
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY c.comic_id, c.title, c.author, c.publisher, c.price, c.stock, c.image_name';

        // Add sorting
        switch ($sort) {
            case 'price-low':
                $sql .= ' ORDER BY c.price ASC';
                break;
            case 'price-high':
                $sql .= ' ORDER BY c.price DESC';
                break;
            case 'title':
                $sql .= ' ORDER BY c.title ASC';
                break;
            case 'newest':
            default:
                $sql .= ' ORDER BY c.comic_id DESC';
                break;
        }

        // Add pagination if limit is set
        if ($limit !== null) {
            $sql .= ' LIMIT ? OFFSET ?';
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }

        try {
            error_log('getFilteredComics SQL: ' . $sql);
            error_log('getFilteredComics Params: ' . print_r($params, true));
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log('getFilteredComics Results count: ' . count($results));
            return $results;
        } catch (PDOException $e) {
            error_log('Error in getFilteredComics: ' . $e->getMessage());
            return [];
        }
    }

    public function getComicCategories($comicId) {
        $sql = 'SELECT cat.* 
                FROM categories cat 
                JOIN comic_categories cc ON cat.category_id = cc.category_id 
                WHERE cc.comic_id = :comic_id 
                ORDER BY cat.name';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['comic_id' => $comicId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getComicReviews($comicId) {
        $sql = 'SELECT r.*, u.name as user_name 
                FROM reviews r 
                JOIN users u ON r.user_id = u.user_id 
                WHERE r.comic_id = :comic_id 
                ORDER BY r.review_id DESC';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['comic_id' => $comicId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalComics() {
        $stmt = $this->pdo->query('SELECT COUNT(*) as total FROM comics');
        return $stmt->fetch()['total'];
    }

    public function getLowStockComics($limit = 5) {
        $stmt = $this->pdo->prepare('SELECT * FROM comics WHERE stock <= 5 ORDER BY stock ASC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getComicsWithFilters($search = '', $category = '', $sort = 'title_asc') {
        $sql = 'SELECT DISTINCT c.*, GROUP_CONCAT(cat.name) as categories 
                FROM comics c 
                LEFT JOIN comic_categories cc ON c.comic_id = cc.comic_id 
                LEFT JOIN categories cat ON cc.category_id = cat.category_id';
        
        $params = [];
        $where = [];

        // Search filter
        if (!empty($search)) {
            $where[] = '(c.title LIKE :search OR c.author LIKE :search)';
            $params['search'] = "%$search%";
        }

        // Category filter
        if (!empty($category)) {
            $where[] = 'cc.category_id = :category_id';
            $params['category_id'] = $category;
        }

        // Add WHERE clause if we have conditions
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        // Group by to avoid duplicates due to multiple categories
        $sql .= ' GROUP BY c.comic_id';

        // Add ORDER BY clause based on sort parameter
        switch ($sort) {
            case 'title_desc':
                $sql .= ' ORDER BY c.title DESC';
                break;
            case 'price_asc':
                $sql .= ' ORDER BY c.price ASC';
                break;
            case 'price_desc':
                $sql .= ' ORDER BY c.price DESC';
                break;
            case 'stock_asc':
                $sql .= ' ORDER BY c.stock ASC';
                break;
            case 'stock_desc':
                $sql .= ' ORDER BY c.stock DESC';
                break;
            case 'title_asc':
            default:
                $sql .= ' ORDER BY c.title ASC';
                break;
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getComicsWithFilters: " . $e->getMessage());
            return [];
        }
    }
} 