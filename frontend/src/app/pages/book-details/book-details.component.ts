import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { forkJoin } from 'rxjs';
import { Book } from 'src/app/models/book.model';
import { Reservation } from 'src/app/models/reservation.model';
import { BookService } from 'src/app/services/book.service';
import { ReservationService } from 'src/app/services/reservation.service';

@Component({
  selector: 'app-book-details',
  templateUrl: './book-details.component.html',
  styleUrls: ['./book-details.component.scss'],
  imports: [CommonModule],
  standalone: true
})
export class BookDetailsComponent implements OnInit {

  book?: Book;
  books: Book[] = []
  userReservations: Reservation[] = [];
  isLoading = false;
  error: string | null = null;
  reserving = false;
  reservationSuccess: string | null = null;
  reservationError: string | null = null;
  reservedBookIds: Set<string> = new Set();

  constructor(
    private route: ActivatedRoute,
    private bookService: BookService,
    private reservationService: ReservationService
  ) {}

  ngOnInit(): void {
    const bookId = this.route.snapshot.paramMap.get('id');
    if (bookId) {
      this.loadBookAndReservations(bookId);
    } else {
      this.error = 'Book ID not found.';
    }
    console.log('Book data received:', this.book);

  }


loadBookAndReservations(bookId: string): void {
  this.isLoading = true;
  forkJoin({
    book: this.bookService.getBook(bookId),
    reservations: this.reservationService.getUserReservations()
  }).subscribe({
    next: ({ book, reservations }) => {
      console.log('Book data received:', book);
      console.log('Reservations data received:', reservations);

      this.book = book;
      this.userReservations = reservations.records;
      this.reservedBookIds = new Set(
        this.userReservations
          .filter(r => r.status === 'RESERVED')
          .map(r => r.book_id)
      );
      

      this.updateBookReservationStatus();

      this.isLoading = false;
      console.log('Book data received:', book);

      this.error = null;
    },
    error: (err) => {
      console.error('Error loading data for BookDetailsComponent:', err);
      if (err.message) {
        this.error = `Failed to load details: ${err.message}`;
      } else if (err.status === 401) {
        this.error = 'Authentication failed. Please log in again.';
      } else if (err.status === 404) {
        this.error = 'Book or reservations not found.';
      } else {
        this.error = 'An unexpected error occurred while loading data.';
      }
      this.isLoading = false;
    }
  });
}

  reserveBook(book: Book): void {
    if (book.reservedByUser || !book.available) {
      return;
    }

    this.reserving = true;
    this.reservationSuccess = null;
    this.reservationError = null;

    this.reservationService.createReservation(book.id).subscribe({
      next: ({ book, reservations }) => {
        console.log('Book data received:', book); 
  this.book = book;
        this.reservationSuccess = 'Book reserved successfully!';
        book.reservedByUser = true;
        book.available = false;
        this.reserving = false;
      },
      error: (err) => {
        console.error('Reservation error response:', err);
        this.reservationError = 'Failed to reserve book. Please try again later.';
        this.reserving = false;
      }
    });

    // window.location.reload();
  }

  updateBookReservationStatus(){
    if (this.book && this.reservedBookIds.has(this.book.id)) {
      this.book.reservedByUser = true;
      this.book.available = false;
    } else if (this.book) {
      this.book.reservedByUser = false;
    }
  }
}