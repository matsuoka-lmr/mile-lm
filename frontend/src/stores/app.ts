import { defineStore } from 'pinia'
import { type LoginUser } from '@/types/data'

type AppState = {
  user: LoginUser | null
  menu: boolean
  initializing: boolean
  redirect: string
  maintenance: number
  title: string
}

export const useAppStore = defineStore('app', {
  state: (): AppState => ({
    user: null,
    menu: true,
    initializing: true,
    redirect: '/',
    maintenance: 0,
    title: ''
  }),
  getters: {
    isLogined: (state) => state.user != null,
    isAdmin: (state) => state.user != null && state.user.role == 99
  }
})
