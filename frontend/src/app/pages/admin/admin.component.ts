import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Book } from 'src/app/models/book.model';
import { Reservation } from 'src/app/models/reservation.model';
import { User } from 'src/app/models/user.model';
import { BookService } from 'src/app/services/book.service';
import { ReservationService } from 'src/app/services/reservation.service';
import { UserService } from 'src/app/services/user.service';

@Component({
  selector: 'app-admin',
  templateUrl: './admin.component.html',
  styleUrls: ['./admin.component.scss'],
  standalone: true,
  imports: [CommonModule, FormsModule]
})
export class AdminComponent implements OnInit {
  activeReservations: Reservation[] = [];
  reservationLogs: any[] = [];
  reservations: Reservation[] = [];
  errorMessage: string = '';
  user?: User;
  books: Book[] = [];
  newBook: Partial<Book> = {}; 
  bookErrorMessage = '';
  bookSuccessMessage = '';
  showDeleteList = false;
  showCreateForm = false;

  constructor(private reservationService: ReservationService,
    private userService: UserService,
    private bookService: BookService
  ) {}

  ngOnInit(): void {
    this.loadReservations();
    this.loadBooks();
  }


loadBooks(): void {
  this.bookService.getBooks().subscribe({
    next: (books) => this.books = books,
    error: () => this.bookErrorMessage = "Failed to load books."
  });
}

toggleDeleteList(): void {
  this.showDeleteList = !this.showDeleteList;
}

createBook(): void {
  if (!this.newBook.title) {
    this.bookErrorMessage = 'Please fill in required fields (title, price).';
    return;
  }

  this.bookService.createBook(this.newBook as Book).subscribe({
    next: (res) => {
      this.bookSuccessMessage = "Book created successfully!";
      this.newBook = {}; 
      this.loadBooks();
    },
    error: (err) => this.bookErrorMessage = err.message || "Failed to create book."
  });
  window.location.reload();
}

deleteBook(bookId: string): void {
  this.bookService.deleteBook(bookId).subscribe({
    next: () => {
      this.books = this.books.filter(book => book.id !== bookId);
      if (this.books.length === 0) {
        this.showDeleteList = false;
      }
    },
    error: (err) => {
      console.error('Delete failed:', err);
    }
  });
  window.location.reload();

}

cancelDelete(): void {
  this.showDeleteList = false;
}

  loadReservations(): void {
    this.reservationService.getAdminReservations().subscribe((resList) => {
      const reservationsWithExtra = resList.records.map(res => {
        const createdAt = new Date(res.created_at);
        const expiryDate = new Date(createdAt);
        expiryDate.setDate(expiryDate.getDate() + 14);
        res.expiry_date = expiryDate.toISOString();
        return res;
      });

      this.activeReservations = reservationsWithExtra.filter(r => r.status === 'RESERVED' || r.status === 'BORROWED');
      this.reservationLogs = reservationsWithExtra.filter(r => r.status === 'RETURNED' || r.status === 'CANCELLED');

      [...this.activeReservations, ...this.reservationLogs].forEach(res => {
        this.userService.getUserById(Number(res.user_id)).subscribe(user => {
          res.user = user;
        });
      });
    }, err => {
      this.errorMessage = 'Failed to load reservations.';
    });
  }  

  loadUser(id: string): void {
    this.userService.getUserById(Number(id)).subscribe({
      next: (data) => {
        this.user = data;
      },
      error: (err) => {
        console.error('Error loading user:', err);
      }
    });
  }

  approveReservation(reservationId: string): void {
    this.reservationService.approveOrCancelReservation(reservationId, 'approve').subscribe({
      next: () => this.loadReservations(),
      error: (err) => {
        this.errorMessage = 'Failed to approve reservation.';
        console.error(err);
      }
    });
  }
  
  cancelReservation(reservationId: string): void {
    this.reservationService.approveOrCancelReservation(reservationId, 'cancel').subscribe({
      next: () => this.loadReservations(),
      error: (err) => {
        this.errorMessage = 'Failed to cancel reservation.';
        console.error(err);
      }
    });
  }  

  markAvailable(reservationId: string, bookId: string): void {
    this.reservationService.returnBook(reservationId, bookId).subscribe({
      next: () => this.loadReservations(),
      error: (err) => {
        this.errorMessage = 'Failed to mark book as available.';
        console.error(err);
      }
    });

    // window.location.reload();
  }
  
}
