import { Component, Input } from "@angular/core"

@Component({
  selector: "app-alert",
  templateUrl: "./alert.component.html",
  styleUrls: ["./alert.component.scss"],
  standalone: false
})
export class AlertComponent {
  @Input() type: "success" | "danger" | "warning" | "info" = "info"
  @Input() message = ""
  @Input() dismissible = true

  isVisible = true

  constructor() {}

  dismiss(): void {
    this.isVisible = false
  }
}
