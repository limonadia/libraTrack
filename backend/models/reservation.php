<?php
class Reservation {
    private $conn;
    private $table_name = "reservations";

    public $id;
    public $book_id;
    public $user_id;
    public $status;
    public $date;
    public $due_date;
    public $expiry_date;
    public $created_at;
    public $updated_at;

    public $book_title;  
    public $book_author;
    public $user_name;  
    public $book_available;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function countByStatusAndUser($status, $userId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE status = :status AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['count'] : 0;
    }

    public function countOverdueByUser($userId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE status = 'BORROWED' AND due_date < NOW() AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['count'] : 0;
    }

    public function create() {
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->book_id = htmlspecialchars(strip_tags($this->book_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->date = date('Y-m-d H:i:s');
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');

        if($this->status == 'BORROWED') {
            $this->due_date = date('Y-m-d H:i:s', strtotime('+14 days')); 
            $this->expiry_date = null;
        } else if($this->status == 'RESERVED') {
            $this->due_date = null;
            $this->expiry_date = date('Y-m-d H:i:s', strtotime('+7 days')); 
        }

        $query = "INSERT INTO " . $this->table_name . "
                SET
                    id = :id,
                    book_id=:book_id,
                    user_id=:user_id,
                    status=:status,
                    date=:date,
                    due_date=:due_date,
                    expiry_date=:expiry_date,
                    created_at=:created_at,
                    updated_at=:updated_at";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":book_id", $this->book_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":due_date", $this->due_date);
        $stmt->bindParam(":expiry_date", $this->expiry_date);
        $stmt->bindParam(":created_at", $this->created_at);
        $stmt->bindParam(":updated_at", $this->updated_at);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            
            $this->updateBookAvailability(false);
            
            return true;
        }

        return false;
    }

    public function read() {
        $query = "SELECT r.*, b.title as book_title, b.author as book_author, u.name as user_name
                  FROM " . $this->table_name . " r
                  LEFT JOIN books b ON r.book_id = b.id
                  LEFT JOIN users u ON r.user_id = u.id
                  ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt;
    }

    public function readByUser() {
        $query = "SELECT r.*, b.title as book_title, b.author as book_author, b.cover_image as book_cover
                  FROM " . $this->table_name . " r
                  LEFT JOIN books b ON r.book_id = b.id
                  WHERE r.user_id = ?
                  ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->user_id);

        $stmt->execute();

        return $stmt;
    }

    public function readOne() {
        $query = "SELECT r.*, b.title as book_title, b.author as book_author, u.name as user_name
                  FROM " . $this->table_name . " r
                  LEFT JOIN books b ON r.book_id = b.id
                  LEFT JOIN users u ON r.user_id = u.id
                  WHERE r.id = ?
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->id);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->book_id = $row['book_id'];
            $this->user_id = $row['user_id'];
            $this->status = $row['status'];
            $this->date = $row['date'];
            $this->due_date = $row['due_date'];
            $this->expiry_date = $row['expiry_date'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            $this->book_title = $row['book_title'];
            $this->book_author = $row['book_author'];
            $this->user_name = $row['user_name'];
            
            return true;
        }

        return false;
    }

    public function update() {
        $current_status = $this->getCurrentStatus();
        
        $query = "UPDATE " . $this->table_name . "
                SET
                    status=:status,
                    due_date=:due_date,
                    expiry_date=:expiry_date,
                    updated_at=:updated_at
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->status = htmlspecialchars(strip_tags((string)$this->status));
        $this->id = htmlspecialchars(strip_tags((string)$this->id));
        $this->updated_at = date('Y-m-d H:i:s');

        if($this->status == 'BORROWED') {
            $this->due_date = date('Y-m-d H:i:s', strtotime('+14 days')); // 14 days loan period
            $this->expiry_date = null;
        } else if($this->status == 'RESERVED') {
            $this->due_date = null;
            $this->expiry_date = date('Y-m-d H:i:s', strtotime('+7 days')); // 7 days reservation period
        } else if($this->status == 'RETURNED' || $this->status == 'CANCELLED') {
            $this->due_date = null;
            $this->expiry_date = null;
        }

        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":due_date", $this->due_date);
        $stmt->bindParam(":expiry_date", $this->expiry_date);
        $stmt->bindParam(":updated_at", $this->updated_at);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            if(($this->status == 'RETURNED' || $this->status == 'CANCELLED') && 
               ($current_status == 'BORROWED' || $current_status == 'RESERVED')) {
                $this->updateBookAvailability(true);
            }
            
            return true;
        }

        return false;
    }

    public function delete() {
        $current_status = $this->getCurrentStatus();
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            if($current_status == 'BORROWED' || $current_status == 'RESERVED') {
                $this->updateBookAvailability(true);
            }
            
            return true;
        }

        return false;
    }
    
    private function getCurrentStatus() {
        $query = "SELECT status FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->id);
        
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? $row['status'] : null;
    }
    
    private function updateBookAvailability($available) {
        $query = "UPDATE books SET available = ? WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $available_value = $available ? 1 : 0;
        $stmt->bindParam(1, $available_value);
        $stmt->bindParam(2, $this->book_id);
        
        $stmt->execute();
    }

    public function isAlreadyReserved() {
        $query = "SELECT COUNT(*) as count FROM reservations WHERE book_id = :book_id AND status = 'reserved'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':book_id', $this->book_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }

    public function getRemindersDueIn($days) {
        $query = "
            SELECT r.id, r.due_date, u.email, u.name as user_name, b.title as book_title
            FROM reservations r
            JOIN users u ON r.user_id = u.id
            JOIN books b ON r.book_id = b.id
            WHERE r.status = 'BORROWED' AND DATE(r.due_date) = DATE_ADD(CURDATE(), INTERVAL ? DAY)
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOverdueBooks() {
        $query = "
            SELECT r.id, r.due_date, u.email, u.name as user_name, b.title as book_title
            FROM reservations r
            JOIN users u ON r.user_id = u.id
            JOIN books b ON r.book_id = b.id
            WHERE r.status = 'BORROWED' AND r.due_date < CURDATE()
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateFine($reservationId, $fine) {
        $query = "UPDATE reservations SET fine = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fine, $reservationId]);
    }

}
?>
