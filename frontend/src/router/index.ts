/**
 * router/index.ts
 *
 * Automatic routes for `./src/pages/*.vue`
 */

// Composables
import {
  createRouter,
  createWebHistory,
  type RouteLocationAsPath,
  type RouteLocationAsRelative,
  type RouteLocationAsString
} from 'vue-router'
import { setupLayouts } from 'virtual:generated-layouts'
import { routes as autoRoutes, handleHotUpdate } from 'vue-router/auto-routes'
import { useAuthStore } from '@/stores'

const Error = () => import('../components/Error.vue')
const routes = autoRoutes.concat([
  {
    name: 'err',
    path: '/error/:code',
    meta: {
      title: 'Error'
    },
    component: Error,
    props: true
  },
  {
    name: 'NotFound',
    path: '/:pathMatch(.*)*',
    redirect: '/error/404'
  }
])

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: setupLayouts(routes)
})

export const canAccess = (to: RouteLocationAsString | RouteLocationAsRelative | RouteLocationAsPath) => {
  const { meta } = router.resolve(to)
  if (meta?.layout == 'auth') return true
  const store = useAuthStore()
  if (!Array.isArray(meta.roles)) return true
  const role = store.user?.role
  return role == 99 || meta.roles.includes(role)
}

router.beforeEach(async (to) => {
  const store = useAuthStore()
  const { meta } = router.resolve(to)
  if (!store.isLogined && meta?.layout != 'auth') await store.reLogin()
  if (store.isLogined && !canAccess(to)) return { path: '/', replace: true }
})

// Workaround for https://github.com/vitejs/vite/issues/11804
router.onError((err, to) => {
  if (err?.message?.includes?.('Failed to fetch dynamically imported module')) {
    if (!localStorage.getItem('vuetify:dynamic-reload')) {
      console.log('Reloading page to fix dynamic import error')
      localStorage.setItem('vuetify:dynamic-reload', 'true')
      location.assign(to.fullPath)
    } else {
      console.error('Dynamic import error, reloading page did not fix it', err)
    }
  } else {
    console.error(err)
  }
})

router.isReady().then(() => {
  localStorage.removeItem('vuetify:dynamic-reload')
})

// This will update routes at runtime without reloading the page
if (import.meta.hot) {
  handleHotUpdate(router)
}

export default router
