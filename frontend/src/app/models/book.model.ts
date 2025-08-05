export interface Book {
  id: string
  title: string
  author: string
  description: string
  category: string
  cover_image: string
  available: boolean
  published_year: number
  isbn: string
  pages: number
  publisher: string
  language: string
  added_by: string
  created_at: string
  updated_at: string
  tags: string[]
  rating: number
  reservedByUser?: boolean;
}
