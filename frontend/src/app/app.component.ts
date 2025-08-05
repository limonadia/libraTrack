import { Component, type OnInit } from "@angular/core"
import { AuthService } from "./services/auth.service"

@Component({
  selector: "app-root",
  templateUrl: "./app.component.html",
  styleUrls: ["./app.component.scss"],
  standalone: false
})
export class AppComponent implements OnInit {
  title = "LibraTrack"

  constructor(private authService: AuthService) {}

  ngOnInit() {
    this.authService.autoLogin()
  }
}
