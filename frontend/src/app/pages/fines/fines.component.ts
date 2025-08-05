import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { Reservation } from 'src/app/models/reservation.model';
import { ReservationService } from 'src/app/services/reservation.service';

@Component({
  selector: 'app-fines',
  templateUrl: './fines.component.html',
  styleUrls: ['./fines.component.scss'],
  standalone: true,
  imports: [CommonModule]
})
export class FinesComponent implements OnInit {

  borrowedBooks: Reservation[] = [];
  overdueBooks: { book: Reservation; fine: number; daysLate: number }[] = [];
  upcomingDueBooks: { book: Reservation; daysLeft: number }[] = [];
  finePerDay = 0.5;

  constructor(private reservationService: ReservationService) {}

  ngOnInit(): void {
    this.reservationService.triggerDailyFinesCheck().subscribe();

    this.reservationService.getUserReservations().subscribe((response) => {
      const today = new Date();

      response.records.forEach((res) => {
        if (res.status === 'BORROWED') {
          const borrowedDate = new Date(res.created_at);
          const daysSinceBorrowed = Math.floor((today.getTime() - borrowedDate.getTime()) / (1000 * 60 * 60 * 24));

          if (daysSinceBorrowed > 30) {
            const daysLate = daysSinceBorrowed - 30;
            const fine = daysLate * this.finePerDay;
            this.overdueBooks.push({ book: res, fine, daysLate });
          } else {
            const daysLeft = 30 - daysSinceBorrowed;
            this.upcomingDueBooks.push({ book: res, daysLeft });
          }
        }
      });
    });
  }

  getTotalFine(): number {
    return this.overdueBooks.reduce((acc, curr) => acc + curr.fine, 0);
  }

}
