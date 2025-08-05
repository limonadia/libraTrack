import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { RouterLink } from '@angular/router';
import { Reservation } from 'src/app/models/reservation.model';
import { ReadingListService } from 'src/app/services/reading-list.service';
import { ReservationService } from 'src/app/services/reservation.service';

@Component({
  selector: 'app-reading-lists',
  templateUrl: './reading-lists.component.html',
  styleUrls: ['./reading-lists.component.scss'],
  standalone: true,
  imports: [CommonModule, RouterLink]
})
export class ReadingListsComponent implements OnInit {
  returnedBooks: Reservation[] = [];
  isLoading = true;
  errorMessage: string | null = null;

  constructor(private reservationService: ReservationService) {}

  ngOnInit(): void {
    this.loadReturnedBooks();
  }

  loadReturnedBooks(): void {
    this.reservationService.getUserReservations().subscribe({
      next: (response) => {
        const allReservations = response.records || [];
        this.returnedBooks = allReservations.filter(
          (r) => r.status === 'RETURNED'
        );
        this.isLoading = false;
      },
      error: (err) => {
        console.error('Error fetching user reservations:', err);
        this.errorMessage = 'Failed to load your books. Please try again.';
        this.isLoading = false;
      },
    });
  }
}