<?php
class Review {
    private $conn;
    private $table_name = "reviews";

    public $id;
    public $book_id;
    public $user_id;
    public $rating;
    public $content;
    public $date;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $this->book_id = htmlspecialchars(strip_tags($this->book_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->rating = htmlspecialchars(strip_tags($this->rating));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->date = date('Y-m-d H:i:s');

        $query = "INSERT INTO " . $this->table_name . "
                SET
                    book_id=:book_id,
                    user_id=:user_id,
                    rating=:rating,
                    content=:content,
                    date=:date";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":book_id", $this->book_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":rating", $this->rating);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":date", $this->date);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function readByBook() {
        $query = "SELECT r.*, u.name as user_name, u.avatar as user_avatar
                  FROM " . $this->table_name . " r
                  LEFT JOIN users u ON r.user_id = u.id
                  WHERE r.book_id = ?
                  ORDER BY r.date DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->book_id);

        $stmt->execute();

        return $stmt;
    }

    public function readByUser() {
        $query = "SELECT r.*, b.title as book_title, b.author as book_author
                  FROM " . $this->table_name . " r
                  LEFT JOIN books b ON r.book_id = b.id
                  WHERE r.user_id = ?
                  ORDER BY r.date DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->user_id);

        $stmt->execute();

        return $stmt;
    }

    public function readOne() {
        $query = "SELECT r.*, u.name as user_name, u.avatar as user_avatar, b.title as book_title
                  FROM " . $this->table_name . " r
                  LEFT JOIN users u ON r.user_id = u.id
                  LEFT JOIN books b ON r.book_id = b.id
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
            $this->rating = $row['rating'];
            $this->content = $row['content'];
            $this->date = $row['date'];
            
            $this->user_name = $row['user_name'];
            $this->user_avatar = $row['user_avatar'];
            $this->book_title = $row['book_title'];
            
            return true;
        }

        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    rating=:rating,
                    content=:content
                WHERE id=:id AND user_id=:user_id";

        $stmt = $this->conn->prepare($query);

        $this->rating = htmlspecialchars(strip_tags($this->rating));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        $stmt->bindParam(":rating", $this->rating);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":user_id", $this->user_id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function delete() {
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
}
?>
