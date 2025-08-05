export interface Review {
  id: string
  book_id: string
  user_id: string
  user_name: string
  user_avatar?: string
  rating: number
  comment: string
  created: string
  modified: string
}

export interface ReviewResponse {
  records: Review[]
}

export interface ReviewFormData {
  book_id: string
  rating: number
  content: string
}
