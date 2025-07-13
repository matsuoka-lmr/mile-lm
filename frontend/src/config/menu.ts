export interface MenuItem {
  title: string
  icon?: string
  to: string
  roles?: number[]
  type?: string
  BgColor?: string
  disabled?: boolean
  subCaption?: string
}

export interface SubMenu {
  header: string
  items: MenuItem[]
}

const Menu: SubMenu[] = [
  {
    header: 'Home',
    items: [
      {
        title: 'トップページ',
        icon: 'mdi-home',
        to: '/'
      }
    ]
  },
  {
    header: '管理者メニュー',
    items: [
      {
        title: 'ショップ管理',
        icon: 'mdi-car-wrench',
        to: '/shop',
        roles: [1, 9]
      },
      {
        title: 'ユーザー管理',
        icon: 'mdi-account',
        to: '/user'
      },
      {
        title: 'デバイス管理',
        icon: 'mdi-map-marker',
        to: '/device'
      }
    ]
  },
  {
    header: '利用者メニュー',
    items: [
      {
        title: '顧客管理',
        icon: 'mdi-account-tie',
        to: '/customer'
      },
      {
        title: '車両管理',
        icon: 'mdi-truck',
        to: '/vehicle'
      }
    ]
  }
]

export default Menu
