import { Component, type OnInit } from "@angular/core"
import { BookService } from "../../services/book.service"
import { Book } from "../../models/book.model"

@Component({
  selector: "app-book-list",
  templateUrl: "./book-list.component.html",
  styleUrls: ["./book-list.component.scss"],
  standalone: false
})
export class BookListComponent implements OnInit {
  books: Book[] = []
  filteredBooks: Book[] = []
  categories: string[] = []
  selectedCategory = ""
  searchTerm = ""
  isLoading = true
  error: string | null = null
  selectedSort = "titleAsc"
  showAvailableOnly = false;

  constructor(private bookService: BookService) {}

  ngOnInit(): void {
    this.loadBooks()
    this.loadCategories()
  }

  loadBooks(): void {
    this.isLoading = true
    this.bookService.getBooks().subscribe({
      next: (books) => {
        this.books = books;
        this.applyFilters();
        this.isLoading = false
      },
      error: (error) => {
        this.error = "Failed to load books. Please try again later."
        this.isLoading = false
        console.error("Error loading books:", error)
      },
    })
  }

  loadCategories(): void {
    this.bookService.getCategories().subscribe({
      next: (categories) => {
        this.categories = categories
      },
      error: (error) => {
        console.error("Error loading categories:", error)
      },
    })
  }

  filterByCategory(category: string): void {
    this.selectedCategory = category
    this.applyFilters()
  }

  onSearch(): void {
    this.applyFilters()
  }

  clearFilters(): void {
    this.selectedCategory = ""
    this.searchTerm = ""
    this.selectedSort = "titleAsc"
    this.showAvailableOnly = false; 
    this.filteredBooks = this.books
  }

  applyFilters(): void {
    this.filteredBooks = this.books.filter((book) => {
      const categoryMatch = !this.selectedCategory || book.category === this.selectedCategory

      const searchLower = this.searchTerm.toLowerCase()
      const searchMatch =
        !this.searchTerm ||
        (book.title?.toLowerCase().includes(searchLower)) ||
        (book.author.toLowerCase().includes(searchLower)) ||
        (book.isbn.toLowerCase().includes(searchLower))

        const availabilityMatch = !this.showAvailableOnly || book.available === true;

      return categoryMatch && searchMatch && availabilityMatch;
    })

    this.filteredBooks.sort((a, b) => {
      switch (this.selectedSort) {
        case "titleAsc":
          return a.title.localeCompare(b.title)
        case "titleDesc":
          return b.title.localeCompare(a.title)
        case "authorAsc":
          return a.author.localeCompare(b.author)
        case "authorDesc":
          return b.author.localeCompare(a.author)
        case "yearAsc":
          return (a.published_year || 0) - (b.published_year || 0)
        case "yearDesc":
          return (b.published_year || 0) - (a.published_year || 0)
        default:
          return 0
      }
    })
  }
}
