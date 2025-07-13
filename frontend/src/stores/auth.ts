import { defineStore } from 'pinia'
import { type User } from '@/types/data'
import { type ID } from '@/common/defines'

import router, { canAccess } from '@/router'
import { useApi } from '@/common/api'
import { useToast } from 'vue-toastification'
import { Config } from '@/config'

type AuthState = {
  user: User | null
  timeout: number
  updatedAt: number
  redir: string
}

const state: AuthState = {
  user: null,
  timeout: 30,
  updatedAt: 0,
  redir: '/'
}

export const useAuthStore = defineStore('auth', {
  state: () => state,
  getters: {
    isLogined: (state) => state.user != null && Date.now() - state.updatedAt < state.timeout * 60 * 1000,
    isAdmin: (state) => state.user != null && state.user.role == 99
  },
  actions: {
    async login(email: string, password: string) {
      const { user, timeout } = await useApi().post<{ user?: User; timeout?: number }>('login', {
        email,
        password
      })

      if (user) {
        // update pinia state
        this.user = user
        if (timeout) this.timeout = timeout
        this.updatedAt = Date.now()
        // redirect to previous url or default to home page
        router.push(canAccess(this.redir) ? this.redir : '/')
        useToast().success('ログインしました')
      }
    },
    async reLogin() {
      if (localStorage.getItem(Config.StoreKeyToken)) {
        const { user, timeout } = await useApi().get<{ user?: User; timeout?: number }>('login')
        if (user) {
          this.user = user
          if (timeout) this.timeout = timeout
          this.updatedAt = Date.now()
        }
      } else this.logout()
    },
    async fake(fakeAs: ID | null) {
      const { user } = await useApi().get<{ user?: User }>(fakeAs == null ? 'fake' : `fake/${fakeAs}`)
      if (user) useToast().success(h('div', {}, [h('strong', {}, `【${user.name}】`), 'としてログインしました']))
      this.user = user || null
    },
    logout() {
      this.user = null
      localStorage.removeItem(Config.StoreKeyToken)
      const path = router.currentRoute.value.path
      if (path != '/login') {
        this.redir = path.startsWith('/err') ? '/' : path
        router.push('/login')
      }
    }
  }
})
