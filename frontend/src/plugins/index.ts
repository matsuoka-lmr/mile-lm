/**
 * plugins/index.ts
 */

// Plugins
import pinia from '../stores'
import router from '../router'
import Toast, { POSITION } from 'vue-toastification'
import 'vue-toastification/dist/index.css'
import vuetify from './vuetify'
import { type App } from 'vue'

export function registerPlugins(app: App) {
  app
    .use(pinia)
    .use(router)
    .use(Toast, {
      timeout: 5000,
      // closeOnClick: true,
      // pauseOnFocusLoss: true,
      // pauseOnHover: true,
      // draggable: false,
      // showCloseButtonOnHover: true,
      // hideProgressBar: false,
      // closeButton: 'button',
      // icon: true,
      position: POSITION.BOTTOM_CENTER
    })
    .use(vuetify)
}
