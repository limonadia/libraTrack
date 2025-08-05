<?php
class ReadingList {
    private $conn;
    private $table_name = "reading_lists";
    private $items_table = "reading_list_items";

    public $id;
    public $name;
    public $user_id;
    public $is_public;
    public $created_at;
    public $updated_at;
    public $items; 

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->is_public = $this->is_public ? 1 : 0;
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');

        $query = "INSERT INTO " . $this->table_name . "
                SET
                    name=:name,
                    user_id=:user_id,
                    is_public=:is_public,
                    created_at=:created_at,
                    updated_at=:updated_at";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":is_public", $this->is_public);
        $stmt->bindParam(":created_at", $this->created_at);
        $stmt->bindParam(":updated_at", $this->updated_at);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            
            if(!empty($this->items)) {
                $this->addItems($this->items);
            }
            
            return true;
        }

        return false;
    }

    public function readByUser() {
        $query = "SELECT rl.*, COUNT(rli.id) as item_count
                  FROM " . $this->table_name . " rl
                  LEFT JOIN " . $this->items_table . " rli ON rl.id = rli.reading_list_id
                  WHERE rl.user_id = ?
                  GROUP BY rl.id
                  ORDER BY rl.created_at DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->user_id);

        $stmt->execute();

        return $stmt;
    }

    public function readPublic() {
        $query = "SELECT rl.*, u.name as user_name, COUNT(rli.id) as item_count
                  FROM " . $this->table_name . " rl
                  LEFT JOIN " . $this->items_table . " rli ON rl.id = rli.reading_list_id
                  LEFT JOIN users u ON rl.user_id = u.id
                  WHERE rl.is_public = 1
                  GROUP BY rl.id
                  ORDER BY rl.created_at DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt;
    }

    public function readOne() {
        $query = "SELECT rl.*, u.name as user_name
                  FROM " . $this->table_name . " rl
                  LEFT JOIN users u ON rl.user_id = u.id
                  WHERE rl.id = ?
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->id);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->user_id = $row['user_id'];
            $this->is_public = $row['is_public'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            $this->user_name = $row['user_name'];
            
            $this->items = $this->getItems();
            
            return true;
        }

        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    name=:name,
                    is_public=:is_public,
                    updated_at=:updated_at
                WHERE id=:id AND user_id=:user_id";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->is_public = $this->is_public ? 1 : 0;
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->updated_at = date('Y-m-d H:i:s');

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":is_public", $this->is_public);
        $stmt->bindParam(":updated_at", $this->updated_at);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":user_id", $this->user_id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function delete() {
        $this->deleteAllItems();
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ? AND user_id = ?";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $this->user_id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }
    
    public function addItems($book_ids) {
        if(empty($book_ids)) {
            return false;
        }
        
        $success = true;
        
        foreach($book_ids as $book_id) {
            $book_id = htmlspecialchars(strip_tags($book_id));
            
            $query = "SELECT id FROM " . $this->items_table . " WHERE reading_list_id = ? AND book_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->bindParam(2, $book_id);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                continue; 
            }
            
            $query = "INSERT INTO " . $this->items_table . " (reading_list_id, book_id, added_at) VALUES (?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            
            $added_at = date('Y-m-d H:i:s');
            
            $stmt->bindParam(1, $this->id);
            $stmt->bindParam(2, $book_id);
            $stmt->bindParam(3, $added_at);
            
            if(!$stmt->execute()) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    public function removeItem($book_id) {
        $book_id = htmlspecialchars(strip_tags($book_id));
        
        $query = "DELETE FROM " . $this->items_table . " WHERE reading_list_id = ? AND book_id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $book_id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    private function deleteAllItems() {
        $query = "DELETE FROM " . $this->items_table . " WHERE reading_list_id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->id);
        
        $stmt->execute();
        
        return true;
    }
    
    private function getItems() {
        $query = "SELECT rli.*, b.title, b.author, b.cover_image, b.category
                  FROM " . $this->items_table . " rli
                  LEFT JOIN books b ON rli.book_id = b.id
                  WHERE rli.reading_list_id = ?
                  ORDER BY rli.added_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->id);
        
        $stmt->execute();
        
        $items = array();
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $item = array(
                "id" => $row['id'],
                "book_id" => $row['book_id'],
                "added_at" => $row['added_at'],
                "title" => $row['title'],
                "author" => $row['author'],
                "cover_image" => $row['cover_image'],
                "category" => $row['category']
            );
            
            $items[] = $item;
        }
        
        return $items;
    }
}
?>
