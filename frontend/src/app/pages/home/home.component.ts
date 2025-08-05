import { Component, type OnInit } from "@angular/core"
import { Book } from "src/app/models/book.model";
import { BookService } from "src/app/services/book.service";

@Component({
  selector: "app-home",
  templateUrl: "./home.component.html",
  styleUrls: ["./home.component.scss"],
  standalone: false
})
export class HomeComponent implements OnInit {
  featuredBooks: Book[] = [];
  isLoading = false;
  error: string | null = null;

  constructor(private bookService: BookService) {}

  ngOnInit(): void {
    this.loadClassicBooks();
  }

  loadClassicBooks(): void {
    this.isLoading = true;
    this.bookService.getBooks().subscribe({
      next: (books) => {
        this.featuredBooks = books.filter(book => 
          book.category?.toLowerCase() === 'classic'
        );
        this.isLoading = false;
      },
      error: (err) => {
        this.error = "Failed to load classic books.";
        this.isLoading = false;
        console.error("Error loading books:", err);
      }
    });
    
  }
}
