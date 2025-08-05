import { Injectable } from "@angular/core"
import { HttpClient } from "@angular/common/http"
import { BehaviorSubject, type Observable, throwError } from "rxjs"
import { catchError, tap } from "rxjs/operators"
import { Router } from "@angular/router"
import { environment } from "../../environments/environment"
import { User, AuthResponse, RegisterData, LoginData } from "../models/user.model"

@Injectable({
  providedIn: "root",
})
export class AuthService {
  private apiUrl = environment.apiUrl
  private tokenExpirationTimer: any
  private _user = new BehaviorSubject<User | null>(null)

  user$ = this._user.asObservable()

  constructor(
    private http: HttpClient,
    private router: Router,
  ) {}

  register(userData: RegisterData): Observable<{ message: string }> {
    return this.http.post<{ message: string }>(`${this.apiUrl}/auth/register.php`, userData, { responseType: 'json' }).pipe(
      catchError((errorRes) => {
        console.error('HTTP Error response:', errorRes);
        return throwError(() => new Error(errorRes.error?.message || "Registration failed"));
      })
    )
  }

  login(userData: LoginData): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.apiUrl}/auth/login.php`, userData).pipe(
      tap((resData) => {
        this.handleAuthentication(
          resData.id,
          resData.name,
          resData.email,
          resData.role,
          resData.avatar,
          resData.jwt,
          resData.expireAt,
        )
      }),
      catchError((errorRes) => {
        console.error("Login HTTP error:", errorRes);
        const errMsg = errorRes.error?.message || errorRes.statusText || "Login failed";
        return throwError(() => new Error(errMsg));
      }),
    )
  }
  

  autoLogin() {
    const userData = localStorage.getItem("userData")
    if (!userData) {
      return
    }

    const parsedData: {
      id: string
      name: string
      email: string
      role: "STUDENT" | "TEACHER" | "STAFF" | "ADMIN"
      avatar?: string
      _token: string
      _tokenExpirationDate: string
    } = JSON.parse(userData)

    const loadedUser: User = {
      id: parsedData.id,
      name: parsedData.name,
      email: parsedData.email,
      role: parsedData.role,
      avatar: parsedData.avatar,
      active: true,
    }

    const expirationDate = new Date(parsedData._tokenExpirationDate)
    if (expirationDate <= new Date()) {
      this.logout()
      return
    }

    this._user.next(loadedUser)

    const expirationDuration = expirationDate.getTime() - new Date().getTime()
    this.autoLogout(expirationDuration)
  }

  logout() {
    this._user.next(null)
    localStorage.removeItem("userData")
    localStorage.removeItem("token")

    if (this.tokenExpirationTimer) {
      clearTimeout(this.tokenExpirationTimer)
    }
    this.tokenExpirationTimer = null

    this.router.navigate(["/login"])
  }

  autoLogout(expirationDuration: number) {
    this.tokenExpirationTimer = setTimeout(() => {
      this.logout()
    }, expirationDuration)
  }

  getToken(): string | null {
    return localStorage.getItem("token")
  }

  isAuthenticated(): boolean {
    return !!this._user.value
  }

  isAdmin(): boolean {
    return this._user.value?.role === "ADMIN"
  }

  isStaff(): boolean {
    return this._user.value?.role === "STAFF" || this._user.value?.role === "ADMIN"
  }

  private handleAuthentication(
    id: string,
    name: string,
    email: string,
    role: string,
    avatar: string | undefined,
    token: string,
    expiresIn: number,
  ) {
    const expirationDate = new Date(expiresIn * 1000)
    const user: User = {
      id,
      name,
      email,
      role: role as "STUDENT" | "TEACHER" | "STAFF" | "ADMIN",
      avatar,
      active: true,
    }

    this._user.next(user)
    this.autoLogout(expirationDate.getTime() - new Date().getTime())

    localStorage.setItem("token", token)
    localStorage.setItem(
      "userData",
      JSON.stringify({
        id,
        name,
        email,
        role,
        avatar,
        _token: token,
        _tokenExpirationDate: expirationDate.toISOString(),
      }),
    )
  }
}
