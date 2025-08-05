import { Component, OnInit } from "@angular/core"
import { FormBuilder, FormGroup, Validators } from "@angular/forms"
import { Router } from "@angular/router"
import { AuthService } from "../../services/auth.service"
import { RegisterData } from "src/app/models/user.model"

@Component({
  selector: "app-register",
  templateUrl: "./register.component.html",
  styleUrls: ["./register.component.scss"],
  standalone: false
})
export class RegisterComponent implements OnInit {
  registerForm!: FormGroup
  isLoading = false
  error: string | null = null
  success: string | null = null

  constructor(
    private formBuilder: FormBuilder,
    private authService: AuthService,
    private router: Router,
  ) {}

  ngOnInit(): void {
    this.registerForm = this.formBuilder.group({
      name: ["", Validators.required],
      email: ["", [Validators.required, Validators.email]],
      password: ["", [Validators.required, Validators.minLength(6)]],
      confirmPassword: ["", Validators.required],
      role: ["STUDENT", Validators.required],
    })

    // Redirect if already logged in
    if (this.authService.isAuthenticated()) {
      this.router.navigate(["/dashboard"])
    }
  }

  onSubmit(): void {
    if (this.registerForm.invalid) {
      return
    }

    if (this.registerForm.value.password !== this.registerForm.value.confirmPassword) {
      this.error = "Passwords do not match"
      return
    }

    this.isLoading = true
    this.error = null
    this.success = null;

    const registerData: RegisterData = {
      name: this.registerForm.value.name,
      email: this.registerForm.value.email,
      password: this.registerForm.value.password,
      role: this.registerForm.value.role
    }

    this.authService.register(registerData).subscribe({
      next: (response) => {
        console.log('Registration success:', response);
        this.success = response.message;
        this.isLoading = false;
        setTimeout(() => {
          this.router.navigate(["/login"]);
        }, 700);
      },
      error: (error) => {
        console.error('Registration error:', error);
        this.error = error.message || "Registration failed";
        this.isLoading = false;
      },
    });
  }
}
