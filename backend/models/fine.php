<?php
class Fine {
    private $conn;
    private $table_name = "fines";

    public $id;
    public $user_id;
    public $book_id;
    public $amount;
    public $status;
    public $due_date;
    public $payment_date;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->book_id = htmlspecialchars(strip_tags($this->book_id));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->due_date = htmlspecialchars(strip_tags($this->due_date));
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');

        $query = "INSERT INTO " . $this->table_name . "
                SET
                    user_id=:user_id,
                    book_id=:book_id,
                    amount=:amount,
                    status=:status,
                    due_date=:due_date,
                    created_at=:created_at,
                    updated_at=:updated_at";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":book_id", $this->book_id);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":due_date", $this->due_date);
        $stmt->bindParam(":created_at", $this->created_at);
        $stmt->bindParam(":updated_at", $this->updated_at);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function read() {
        $query = "SELECT f.*, b.title as book_title, u.name as user_name
                  FROM " . $this->table_name . " f
                  LEFT JOIN books b ON f.book_id = b.id
                  LEFT JOIN users u ON f.user_id = u.id
                  ORDER BY f.created_at DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt;
    }

    public function readByUser() {
        $query = "SELECT f.*, b.title as book_title, b.author as book_author
                  FROM " . $this->table_name . " f
                  LEFT JOIN books b ON f.book_id = b.id
                  WHERE f.user_id = ?
                  ORDER BY f.created_at DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->user_id);

        $stmt->execute();

        return $stmt;
    }

    public function readOne() {
        $query = "SELECT f.*, b.title as book_title, u.name as user_name
                  FROM " . $this->table_name . " f
                  LEFT JOIN books b ON f.book_id = b.id
                  LEFT JOIN users u ON f.user_id = u.id
                  WHERE f.id = ?
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->id);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->book_id = $row['book_id'];
            $this->amount = $row['amount'];
            $this->status = $row['status'];
            $this->due_date = $row['due_date'];
            $this->payment_date = $row['payment_date'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            $this->book_title = $row['book_title'];
            $this->user_name = $row['user_name'];
            
            return true;
        }

        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    amount=:amount,
                    status=:status,
                    payment_date=:payment_date,
                    updated_at=:updated_at
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->updated_at = date('Y-m-d H:i:s');

        if($this->status == 'PAID') {
            $this->payment_date = date('Y-m-d H:i:s');
        }

        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":payment_date", $this->payment_date);
        $stmt->bindParam(":updated_at", $this->updated_at);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }
    
    public static function calculateFine($due_date, $fine_rate = 0.5) {
        $due = new DateTime($due_date);
        $now = new DateTime();
        
        if($now <= $due) {
            return 0;
        }
        
        $interval = $now->diff($due);
        $days_overdue = $interval->days;
        
        $fine = $days_overdue * $fine_rate;
        
        return $fine;
    }
}
?>
