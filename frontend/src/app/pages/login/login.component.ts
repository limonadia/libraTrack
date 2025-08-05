import { Component, OnInit } from "@angular/core"
import { FormBuilder, FormGroup, Validators } from "@angular/forms"
import { Router, ActivatedRoute } from "@angular/router"
import { AuthService } from "../../services/auth.service"

@Component({
  selector: "app-login",
  templateUrl: "./login.component.html",
  styleUrls: ["./login.component.scss"],
  standalone: false
})
export class LoginComponent implements OnInit {
  loginForm!: FormGroup
  isLoading = false
  error: string | null = null
  returnUrl = "/dashboard"

  constructor(
    private formBuilder: FormBuilder,
    private authService: AuthService,
    private router: Router,
    private route: ActivatedRoute,
  ) {}

  ngOnInit(): void {
    this.loginForm = this.formBuilder.group({
      email: ["", [Validators.required, Validators.email]],
      password: ["", Validators.required],
    })

    this.returnUrl = this.route.snapshot.queryParams["returnUrl"] || "/dashboard"

    if (this.authService.isAuthenticated()) {
      this.router.navigate([this.returnUrl])
    }
  }

  onSubmit(): void {
    if (this.loginForm.invalid) {
      return
    }

    this.isLoading = true
    this.error = null

    const loginData = {
      email: this.loginForm.value.email,
      password: this.loginForm.value.password,
    }

    this.authService.login(loginData).subscribe({
      next: (res) => {
        console.log("Login successful, navigating to:", this.returnUrl, "Response:", res);
        this.router.navigate([this.returnUrl]);
      },
      error: (error) => {
        console.log("Login error:", error);
        this.error = error.message || "Login failed";
        this.isLoading = false;
      },
      complete: () => {
        console.log("Login observable completed");
      }
    });
    
  }
}
