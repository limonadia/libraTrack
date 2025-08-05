import { HttpClient } from "@angular/common/http"
import { Injectable } from "@angular/core"
import { Observable, throwError } from "rxjs"
import { catchError } from "rxjs/operators"
import { environment } from "../../environments/environment"
import { ReservationResponse, ReservationFormData, Reservation } from "../models/reservation.model"

@Injectable({
  providedIn: "root",
})
export class ReservationService {
  private apiUrl = environment.apiUrl

  constructor(private http: HttpClient) {}

  getUserReservations(): Observable<ReservationResponse> {
    return this.http.get<ReservationResponse>(`${this.apiUrl}/reservations/read_by_user.php`).pipe(
      catchError((errorRes) => {
        return throwError(() => new Error(errorRes.error.message || "Failed to fetch reservations"))
      }),
    )
  }

  createReservation(bookId: string): Observable<any> {
    const body = { book_id: bookId, status: 'reserved' };
    return this.http.post(`${this.apiUrl}/reservations/create.php`, body).pipe(
      catchError(err => {
        console.error('Error creating reservation', err);
        return throwError(() => err);
      })
    );
  }

  updateReservation(id: string, bookId: string, status: string): Observable<{ message: string }> {
    const payload = {
      id: id,
      book_id: bookId, 
      status: status
    };

    return this.http.post<{ message: string }>(`${this.apiUrl}/reservations/update.php`, payload).pipe(
      catchError((errorRes) => {
        return throwError(() => new Error(errorRes.error?.message || "Failed to update reservation"));
      }),
    );
  }

  getAdminReservations(): Observable<{ records: Reservation[] }> {
    return this.http.get<{ records: Reservation[] }>(`${this.apiUrl}/reservations/read_by_admin.php`);
  }

  cancelReservation(id: string, bookId: string): Observable<{ message: string }> {
    return this.updateReservation(id, bookId, "CANCELLED");
  }

  returnBook(reservationId: string, bookId: string): Observable<{ message: string }> {
    const payload = {
      id: reservationId,
      book_id: bookId
    };
  
    return this.http.post<{ message: string }>(`${this.apiUrl}/reservations/admin_return.php`, payload).pipe(
      catchError((errorRes) => {
        return throwError(() => new Error(errorRes.error?.message || 'Failed to return book'));
      })
    );
  }  

  approveOrCancelReservation(id: string, action: 'approve' | 'cancel'): Observable<{ message: string }> {
    const payload = { id, action };
  
    return this.http.post<{ message: string }>(`${this.apiUrl}/reservations/admin_update.php`, payload).pipe(
      catchError((errorRes) => {
        return throwError(() => new Error(errorRes.error?.message || "Failed to update reservation"));
      })
    );
  }

  triggerDailyFinesCheck() {
    return this.http.get(`${this.apiUrl}/fines/run_maintenance.php`);
  }
  
}
