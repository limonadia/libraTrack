import { Component, type OnInit, type OnDestroy } from "@angular/core"
import { Subscription } from "rxjs"
import { AuthService } from "../../services/auth.service"
import { User } from "../../models/user.model"

@Component({
  selector: "app-header",
  templateUrl: "./header.component.html",
  styleUrls: ["./header.component.scss"],
  standalone: false
})
export class HeaderComponent implements OnInit, OnDestroy {
  isAuthenticated = false
  isAdmin = false
  user: User | null = null
  userDropdownOpen = false
  mobileMenuOpen = false
  private userSub: Subscription | undefined

  constructor(private authService: AuthService) {}

  ngOnInit(): void {
    this.userSub = this.authService.user$.subscribe((user) => {
      this.isAuthenticated = !!user
      this.isAdmin = user?.role === "STAFF"
      this.user = user
    })
  }

  ngOnDestroy(): void {
    if (this.userSub) {
      this.userSub.unsubscribe()
    }
  }

  onLogout(): void {
    this.authService.logout()
    this.userDropdownOpen = false
  }

  toggleUserDropdown(): void {
    this.userDropdownOpen = !this.userDropdownOpen
  }

  toggleMobileMenu(): void {
    this.mobileMenuOpen = !this.mobileMenuOpen
  }

  closeMobileMenu(): void {
    this.mobileMenuOpen = false
  }
}
