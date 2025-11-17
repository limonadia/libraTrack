<?php
class Book {
    private $conn;
    private $table_name = "books";

    public $id;
    public $title;
    public $author;
    public $description;
    public $category;
    public $cover_image;
    public $available;
    public $published_year;
    public $isbn;
    public $pages;
    public $publisher;
    public $language;
    public $added_by;
    public $created_at;
    public $updated_at;
    public $tags;
    public $rating;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->cover_image = htmlspecialchars(strip_tags($this->cover_image));
        $this->available = $this->available ? 1 : 0;
        $this->published_year = htmlspecialchars(strip_tags($this->published_year));
        $this->isbn = htmlspecialchars(strip_tags($this->isbn));
        $this->pages = htmlspecialchars(strip_tags($this->pages));
        $this->publisher = htmlspecialchars(strip_tags($this->publisher));
        $this->language = htmlspecialchars(strip_tags($this->language));
        $this->added_by = htmlspecialchars(strip_tags($this->added_by));
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');

        $query = "INSERT INTO " . $this->table_name . "
                SET
                    id=:id,
                    title=:title,
                    author=:author,
                    description=:description,
                    category=:category,
                    cover_image=:cover_image,
                    available=:available,
                    published_year=:published_year,
                    isbn=:isbn,
                    pages=:pages,
                    publisher=:publisher,
                    language=:language,
                    added_by=:added_by,
                    created_at=:created_at,
                    updated_at=:updated_at";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":author", $this->author);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":cover_image", $this->cover_image);
        $stmt->bindParam(":available", $this->available);
        $stmt->bindParam(":published_year", $this->published_year);
        $stmt->bindParam(":isbn", $this->isbn);
        $stmt->bindParam(":pages", $this->pages);
        $stmt->bindParam(":publisher", $this->publisher);
        $stmt->bindParam(":language", $this->language);
        $stmt->bindParam(":added_by", $this->added_by);
        $stmt->bindParam(":created_at", $this->created_at);
        $stmt->bindParam(":updated_at", $this->updated_at);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            
            if(!empty($this->tags)) {
                $this->addTags();
            }
            
            return true;
        }

        return false;
    }

    public function read() {
        $query = "SELECT b.*, 
                    (SELECT AVG(r.rating) FROM reviews r WHERE r.book_id = b.id) as rating
                  FROM " . $this->table_name . " b
                  ORDER BY b.created_at DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt;
    }

    public function readOne() {
        $query = "SELECT b.*, 
                    (SELECT AVG(r.rating) FROM reviews r WHERE r.book_id = b.id) as rating
                  FROM " . $this->table_name . " b
                  WHERE b.id = ?
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->id);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->title = $row['title'];
            $this->author = $row['author'];
            $this->description = $row['description'];
            $this->category = $row['category'];
            $this->cover_image = $row['cover_image'];
            $this->available = $row['available'];
            $this->published_year = $row['published_year'];
            $this->isbn = $row['isbn'];
            $this->pages = $row['pages'];
            $this->publisher = $row['publisher'];
            $this->language = $row['language'];
            $this->added_by = $row['added_by'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            $this->rating = $row['rating'];
            
            $this->tags = $this->getTags();
            
            return true;
        }

        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    title=:title,
                    author=:author,
                    description=:description,
                    category=:category,
                    cover_image=:cover_image,
                    available=:available,
                    published_year=:published_year,
                    isbn=:isbn,
                    pages=:pages,
                    publisher=:publisher,
                    language=:language,
                    updated_at=:updated_at
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->cover_image = htmlspecialchars(strip_tags($this->cover_image));
        $this->available = $this->available ? 1 : 0;
        $this->published_year = htmlspecialchars(strip_tags($this->published_year));
        $this->isbn = htmlspecialchars(strip_tags($this->isbn));
        $this->pages = htmlspecialchars(strip_tags($this->pages));
        $this->publisher = htmlspecialchars(strip_tags($this->publisher));
        $this->language = htmlspecialchars(strip_tags($this->language));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->updated_at = date('Y-m-d H:i:s');

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":author", $this->author);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":cover_image", $this->cover_image);
        $stmt->bindParam(":available", $this->available);
        $stmt->bindParam(":published_year", $this->published_year);
        $stmt->bindParam(":isbn", $this->isbn);
        $stmt->bindParam(":pages", $this->pages);
        $stmt->bindParam(":publisher", $this->publisher);
        $stmt->bindParam(":language", $this->language);
        $stmt->bindParam(":updated_at", $this->updated_at);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            if(!empty($this->tags)) {
                $this->deleteTags();
                $this->addTags();
            }
            
            return true;
        }

        return false;
    }

    public function delete() {
        $this->deleteTags();
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function search($keywords, $category = "", $available = false) {
        $query = "SELECT b.*, 
                    (SELECT AVG(r.rating) FROM reviews r WHERE r.book_id = b.id) as rating
                  FROM " . $this->table_name . " b
                  WHERE (b.title LIKE ? OR b.author LIKE ? OR b.description LIKE ?)";
        
        if(!empty($category)) {
            $query .= " AND b.category = ?";
        }
        
        if($available) {
            $query .= " AND b.available = 1";
        }
        
        $query .= " ORDER BY b.created_at DESC";

        $stmt = $this->conn->prepare($query);

        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";

        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);
        
        if(!empty($category)) {
            $category = htmlspecialchars(strip_tags($category));
            $stmt->bindParam(4, $category);
        }

        $stmt->execute();

        return $stmt;
    }

    public function getCategories() {
        $query = "SELECT DISTINCT category FROM " . $this->table_name . " ORDER BY category";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt;
    }
    
    private function addTags() {
        if(empty($this->tags)) {
            return false;
        }
        
        foreach($this->tags as $tag_name) {
            $tag_name = htmlspecialchars(strip_tags($tag_name));
            
            $query = "INSERT INTO book_tags (book_id, name) VALUES (?, ?)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(1, $this->id);
            $stmt->bindParam(2, $tag_name);
            
            $stmt->execute();
        }
        
        return true;
    }
    
    private function deleteTags() {
        $query = "DELETE FROM book_tags WHERE book_id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->id);
        
        $stmt->execute();
        
        return true;
    }
    
    public function getTags() {
        $query = "SELECT name FROM book_tags WHERE book_id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->id);
        
        $stmt->execute();
        
        $tags = array();
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tags[] = $row['name'];
        }
        
        return $tags;
    }
}
?>
