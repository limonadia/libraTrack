import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ReadingList } from '../models/reading-list.model';
import { environment } from 'src/environments/environment'; 

@Injectable({
  providedIn: 'root'
})
export class ReadingListService {
  private apiUrl = `${environment.apiUrl}/reading_list`; 

  constructor(private http: HttpClient) { }

  createReadingList(name: string, is_public: boolean = false): Observable<{ message: string, id: number }> {
    return this.http.post<{ message: string, id: number }>(`${this.apiUrl}/create.php`, { name, is_public });
  }

  getReadingLists(): Observable<{ records: ReadingList[] }> {
    return this.http.get<{ records: ReadingList[] }>(`${this.apiUrl}/read_by_user.php`);
  }

  getReadingListDetails(id: number): Observable<ReadingList> {
    return this.http.get<ReadingList>(`${this.apiUrl}/read_one.php?id=${id}`);
  }

  addBookToReadingList(readingListId: number, bookId: number): Observable<{ message: string }> {
    return this.http.post<{ message: string }>(`${this.apiUrl}/add_item.php`, { reading_list_id: readingListId, book_id: bookId });
  }

  removeBookFromReadingList(readingListId: number, bookId: number): Observable<{ message: string }> {
    return this.http.post<{ message: string }>(`${this.apiUrl}/remove_item.php`, { reading_list_id: readingListId, book_id: bookId });
  }

  updateReadingList(id: number, name: string, is_public: boolean): Observable<{ message: string }> {
    return this.http.put<{ message: string }>(`${this.apiUrl}/update.php`, { id, name, is_public });
  }

  deleteReadingList(id: number): Observable<{ message: string }> {
    return this.http.post<{ message: string }>(`${this.apiUrl}/delete.php`, { id }); 
  }

  getBorrowedAndReturnedBooks(): Observable<any> {
    return this.http.get<any>(`${environment.apiUrl}/reservations/read_by_user.php`); 
  }
}