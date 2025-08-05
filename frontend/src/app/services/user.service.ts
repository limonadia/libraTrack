import { HttpClient } from "@angular/common/http"
import { Injectable } from "@angular/core"
import { type Observable, throwError } from "rxjs"
import { catchError } from "rxjs/operators"
import { environment } from "../../environments/environment"
import { ReviewResponse, ReviewFormData } from "../models/review.model"
import { User } from "../models/user.model"
import { UserStats } from "../models/userStats.model"

@Injectable({
  providedIn: "root",
})
export class UserService {
  private apiUrl = environment.apiUrl

  constructor(private http: HttpClient) {}

  getUserById(userId: number): Observable<User> {
    return this.http.get<User>(`${this.apiUrl}/users/admin_one_user.php?user_id=${userId}`);
  }

  getUserStats(): Observable<UserStats> {
    return this.http.get<UserStats>(`${this.apiUrl}/users/stats.php`, { withCredentials: true });
  }

}
