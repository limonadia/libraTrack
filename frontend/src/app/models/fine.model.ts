export interface Fine {
  id: string
  user_id: string
  reservation_id: string
  book_title?: string
  amount: number
  reason: string
  status: "PENDING" | "PAID"
  created: string
  modified: string
}
