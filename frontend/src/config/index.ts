import Menu from './menu'

const bool = (str: string) => str.toLowerCase() === 'true'

export const Config = {
  AppName: import.meta.env.VITE_APP_NAME,
  Debug: bool(import.meta.env.VITE_APP_DEBUG),
  API: import.meta.env.VITE_APP_BACKEND_URL ? `${import.meta.env.VITE_APP_BACKEND_URL}/api` : '/api',
  StoreKeyToken: `${import.meta.env.VITE_APP_STORE_KEY}_token`,
  StoreKeyFlash: `${import.meta.env.VITE_APP_STORE_KEY}_flash`,
  List: {
    ItemsPerPage: 10
  },
  Menu
}
