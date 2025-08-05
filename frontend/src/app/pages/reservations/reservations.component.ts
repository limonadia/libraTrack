import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { Reservation } from 'src/app/models/reservation.model';
import { ReservationService } from 'src/app/services/reservation.service';

@Component({
  selector: 'app-reservations',
  templateUrl: './reservations.component.html',
  styleUrls: ['./reservations.component.scss'],
  standalone: true,
  imports: [CommonModule]
})
export class ReservationsComponent implements OnInit {

  reservations: Reservation[] = [];
  reservedBooks: Reservation[] = [];
  borrowedBooks: Reservation[] = [];
  returnedBooks: Reservation[] = [];
  error: string = '';

  constructor(private reservationService: ReservationService) {}

  ngOnInit(): void {
    this.loadReservations();
  }

  loadReservations() {
    this.reservationService.getUserReservations().subscribe({
      next: (res) => {
        this.reservations = res.records;
        this.reservedBooks = this.reservations.filter(r => r.status === 'RESERVED');
        this.borrowedBooks = this.reservations.filter(r => r.status === 'BORROWED');
      },
      error: (err) => {
        this.error = err.message || 'Failed to load reservations';
      }
    });
  }

  cancelReservation(reservationId: string, bookId: string): void {
    if (!confirm('Are you sure you want to cancel this reservation?')) {
      return; 
    }


  const reservation = this.reservations.find(res => res.book_id === bookId);
  if (!reservation) {
    this.error = 'Reservation not found.';
    return;
  }

    this.reservationService.updateReservation(reservationId, bookId, 'CANCELLED').subscribe({
      next: (response) => {
        console.log('Reservation cancelled successfully:', response);
        this.reservedBooks = this.reservedBooks.filter(res => res.id !== reservationId);
        this.loadReservations(); 
        this.error = ''; 
      },
      error: (err) => {
        this.error = err.error?.message || 'Failed to cancel reservation. Please try again.';
        console.error('Error cancelling reservation:', err);
      }
    });
  }
  
}
