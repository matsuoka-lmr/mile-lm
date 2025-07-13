export interface Company {
  id: string
  name: string
  email: string
  phone?: string
  address?: string
  status: number
}

export interface User {
  id: string
  role: number
  company: Company
  name: string
  password?: string
  email: string
  phone?: string
  address?: string
  status: number
}

export interface LoginUser extends User {
  token: string
}
