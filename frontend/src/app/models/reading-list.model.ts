export interface ReadingList {
  id: string
  user_id: string
  name: string
  description: string
  is_public: boolean
  created: string
  modified: string
  books?: ReadingListItem[]
}

export interface ReadingListItem {
  id: number
  reading_list_id: number
  book_id: number
  book_title?: string
  book_author?: string
  book_cover?: string
  added_date: string
}
