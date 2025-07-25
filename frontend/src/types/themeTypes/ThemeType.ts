export type ThemeTypes = {
  name: string
  dark: boolean
  variables?: object
  colors: {
    primary?: string
    secondary?: string
    info?: string
    success?: string
    warning?: string
    error?: string
    indigo?: string
    lightprimary?: string
    lightsecondary?: string
    lightsuccess?: string
    lighterror?: string
    lightinfo?: string
    lightwarning?: string
    lightindigo?: string
    textPrimary?: string
    textSecondary?: string
    borderColor?: string
    hoverColor?: string
    inputBorder?: string
    containerBg?: string
    background?: string
    surface?: string
    grey100?: string
    grey200?: string
    darkbg?: string
    bglight?: string
    bgdark?: string
  }
}
