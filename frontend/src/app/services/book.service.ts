import { HttpClient } from "@angular/common/http"
import { type Observable, throwError } from "rxjs"
import { catchError, map } from "rxjs/operators"
import { environment } from "../../environments/environment"
import { Book } from "../models/book.model"
import { Injectable } from "@angular/core"

@Injectable({
  providedIn: "root",
})
export class BookService {
  private apiUrl = environment.apiUrl

  constructor(private http: HttpClient) {}

  getBooks(): Observable<Book[]> {
    return this.http.get<{ records: Book[] }>(`${this.apiUrl}/books/read.php`).pipe(
      map((response) => response.records),
      catchError((error) => {
        console.error("Error fetching books:", error)
        return throwError(() => new Error("Failed to load books. Please try again later."))
      }),
    )
  }

  getBook(id: string): Observable<Book> {
    return this.http.get<Book>(`${this.apiUrl}/books/read_one.php?id=${id}`).pipe(
      catchError((error) => {
        console.error(`Error fetching book with ID ${id}:`, error);
        return throwError(() => new Error("Failed to load book details. Please try again later."));
      }),
    );
  }
  

  searchBooks(query: string): Observable<Book[]> {
    return this.http.get<{ records: Book[] }>(`${this.apiUrl}/books/search.php?s=${query}`).pipe(
      map((response) => response.records),
      catchError((error) => {
        console.error("Error searching books:", error)
        return throwError(() => new Error("Failed to search books. Please try again later."))
      }),
    )
  }

  getCategories(): Observable<string[]> {
    return this.http.get<string[]>(`${this.apiUrl}/books/categories.php`)
  }

  createBook(book: Book): Observable<{ message: string; id: string }> {
    return this.http.post<{ message: string; id: string }>(`${this.apiUrl}/books/create.php`, book).pipe(
      catchError((error) => {
        console.error("Error creating book:", error)
        return throwError(() => new Error("Failed to create book. Please try again later."))
      }),
    )
  }

  updateBook(book: Book): Observable<{ message: string }> {
    return this.http.post<{ message: string }>(`${this.apiUrl}/books/update.php`, book).pipe(
      catchError((error) => {
        console.error("Error updating book:", error)
        return throwError(() => new Error("Failed to update book. Please try again later."))
      }),
    )
  }

  deleteBook(id: string): Observable<{ message: string }> {
    return this.http.post<{ message: string }>(`${this.apiUrl}/books/delete.php`, { id }).pipe(
      catchError((error) => {
        console.error(`Error deleting book with ID ${id}:`, error)
        return throwError(() => new Error("Failed to delete book. Please try again later."))
      }),
    )
  }
}
