import { HttpClient } from "@angular/common/http"
import { Injectable } from "@angular/core"
import { type Observable, throwError } from "rxjs"
import { catchError } from "rxjs/operators"
import { environment } from "../../environments/environment"
import { ReviewResponse, ReviewFormData } from "../models/review.model"

@Injectable({
  providedIn: "root",
})
export class ReviewService {
  private apiUrl = environment.apiUrl

  constructor(private http: HttpClient) {}

  getBookReviews(bookId: string): Observable<ReviewResponse> {
    return this.http.get<ReviewResponse>(`${this.apiUrl}/reviews/read_by_book.php?book_id=${bookId}`).pipe(
      catchError((errorRes) => {
        return throwError(() => new Error(errorRes.error.message || "Failed to fetch reviews"))
      }),
    )
  }

  createReview(reviewData: ReviewFormData): Observable<{ message: string }> {
    return this.http.post<{ message: string }>(`${this.apiUrl}/reviews/create.php`, reviewData).pipe(
      catchError((errorRes) => {
        return throwError(() => new Error(errorRes.error.message || "Failed to create review"))
      }),
    )
  }
}
