import { NgModule } from "@angular/core"
import { RouterModule, type Routes } from "@angular/router"
import { HomeComponent } from "./pages/home/home.component"
import { LoginComponent } from "./pages/login/login.component"
import { RegisterComponent } from "./pages/register/register.component"
import { BookListComponent } from "./pages/book-list/book-list.component"
import { DashboardComponent } from "./pages/dashboard/dashboard.component"
import { AuthGuard } from "./guards/auth.guard"
import { BookDetailsComponent } from "./pages/book-details/book-details.component"
import { ReservationsComponent } from "./pages/reservations/reservations.component"
import { ReadingListService } from "./services/reading-list.service"
import { ReadingListsComponent } from "./pages/reading-lists/reading-lists.component"
import { FinesComponent } from "./pages/fines/fines.component"
import { ProfileComponent } from "./pages/profile/profile.component"
import { AdminComponent } from "./pages/admin/admin.component"

const routes: Routes = [
  { path: "", component: HomeComponent },
  { path: "login", component: LoginComponent },
  { path: "register", component: RegisterComponent },
  { path: "books", component: BookListComponent },
  { path: "books/:id", component: BookDetailsComponent },
  { path: "reservations", component: ReservationsComponent },
  { path: "reading-lists", component: ReadingListsComponent },
  { path: "fines", component: FinesComponent },
  { path: "profile", component: ProfileComponent },
  { path: "admin", component: AdminComponent },
  {
    path: "dashboard",
    component: DashboardComponent,
    canActivate: [AuthGuard],
  },
  { path: "**", redirectTo: "" },
]

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule],
})
export class AppRoutingModule {}
