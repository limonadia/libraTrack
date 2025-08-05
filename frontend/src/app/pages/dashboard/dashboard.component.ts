import { Component, type OnInit } from "@angular/core"
import { AuthService } from "../../services/auth.service"
import { User } from "../../models/user.model"
import { UserStats } from "src/app/models/userStats.model";
import { UserService } from "src/app/services/user.service";

@Component({
  selector: "app-dashboard",
  templateUrl: "./dashboard.component.html",
  styleUrls: ["./dashboard.component.scss"],
  standalone: false
})
export class DashboardComponent implements OnInit {
  user: User | null = null
  errorMessage: string = '';
  stats: UserStats = {
    borrowedBooks: 0,
    overdueBooks: 0,
    reservations: 0,
  };

  constructor(private authService: AuthService,
    private userService: UserService
  ) {}

  ngOnInit(): void {
    this.authService.user$.subscribe((user) => {
      this.user = user
    })

    this.loadStats()
  }

  private loadStats(): void {
    this.userService.getUserStats().subscribe({
      next: (data) => {
        this.stats = data;
      },
      error: (err) => {
        this.errorMessage = 'Failed to load stats';
        console.error(err);
      }
    });
  }
}
