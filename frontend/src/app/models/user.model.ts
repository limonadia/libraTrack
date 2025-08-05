export interface User {
  id: string
  name: string
  email: string
  role: "STUDENT" | "TEACHER" | "STAFF" | "ADMIN"
  avatar?: string
  active: boolean
}

export interface LoginData {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  role: "STUDENT" | "TEACHER" | "STAFF" | "ADMIN"
}

export interface AuthResponse {
  id: string
  name: string
  email: string
  role: "STUDENT" | "TEACHER" | "STAFF" | "ADMIN"
  avatar?: string
  jwt: string
  expireAt: number
  message: string
}
