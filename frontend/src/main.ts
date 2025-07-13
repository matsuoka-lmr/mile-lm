// Plugins
import { registerPlugins } from '@/plugins'

// Components
import App from './App.vue'

// Composables
import { createApp } from 'vue'

// styles
import '@/scss/style.scss';

const app = createApp(App)
registerPlugins(app)
app.mount('#app')
