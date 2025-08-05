import { User } from "./user.model"

export interface Reservation {
  id: string
  user_id: string
  book_id: string
  book_title?: string
  book_cover?: string
  reservation_date: string
  due_date: string
  return_date: string | null
  status: "RESERVED" | "BORROWED" | "CANCELLED" | "OVERDUE" | "RETURNED",
  date: string
  expiry_date: string
  book: {
    title: string;
    author: string;
    cover_image: string;
  };
  created_at: string
  updated_at: string
  modified: string
  user?: User;
}

export interface ReservationResponse {
  records: Reservation[]
}

export interface ReservationFormData {
  book_id: string
  status: "RESERVED" | "BORROWED"
}