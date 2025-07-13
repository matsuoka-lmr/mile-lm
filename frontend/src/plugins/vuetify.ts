/**
 * plugins/vuetify.ts
 *
 * Framework documentation: https://vuetifyjs.com`
 */

// Styles
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

// Composables
import { createVuetify } from 'vuetify'
import { ja, en } from 'vuetify/locale'

// Theme
import { BLUE_THEME } from '@/theme/LightTheme'

// https://vuetifyjs.com/en/introduction/why-vuetify/#feature-guides
export default createVuetify({
  locale: {
    locale: 'ja',
    fallback: 'en',
    messages: { ja, en }
  },
  theme: {
    defaultTheme: 'BLUE_THEME',
    themes: {
      BLUE_THEME
    }
  },
  defaults: {
    VCard: {
      rounded: 'xl'
    },
    VTextField: {
      variant: 'outlined',
      density: 'comfortable',
      color: 'primary'
    },
    VTextarea: {
      variant: 'outlined',
      density: 'comfortable',
      color: 'primary'
    },
    VSelect: {
      variant: 'outlined',
      density: 'comfortable',
      color: 'primary'
    },
    VListItem: {
      minHeight: '45px'
    },
    VTooltip: {
      location: 'top'
    }
  }
})