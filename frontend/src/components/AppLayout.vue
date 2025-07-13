<script setup lang="ts">
import { Config } from '@/config'
import { useAuthStore } from '@/stores/auth'
import { canAccess } from '@/router'

const sDrawer = ref(true)
const { user, logout } = useAuthStore()
const checkRole = (item: { roles?: number[]; to?: string }) =>
  user?.role && (user.role == 99 || !item.roles || item.roles.includes(user.role)) && item.to && canAccess(item.to)
</script>

<template>
  <!-- App Menu -->
  <v-navigation-drawer v-model="sDrawer" left app class="leftSidebar bg-containerBg" elevation="10" width="270">
    <div class="pa-5 pl-4">
      <div class="logo">
        <img src="https://mamo-l.jp/source/img/logo.png" alt="home" />
        <div class="mt-3 text-h5 font-weight-bold text-pre-wrap">{{ Config.AppName }}</div>
      </div>
    </div>
    <!-- ---------------------------------------------- -->
    <!---Navigation -->
    <!-- ---------------------------------------------- -->
    <div perfect-scrollbar class="scrollnavbar bg-containerBg overflow-y-hidden">
      <v-list class="py-4 px-4 bg-containerBg">
        <!---Menu Loop -->
        <template v-for="(menu, mi) in Config.Menu">
          <template v-if="menu.items.some(checkRole)">
            <v-list-subheader v-if="menu.header" :key="mi" class="smallCap text-capitalize text-subtitle-1 mt-5 d-flex align-items-center">
              <span class="mini-icon"><v-icon icon="mdi-dots-horizontal" size="16" stroke-width="1.5" class="iconClass" /></span>
              <span class="mini-text font-weight-semibold pl-2 text-medium-emphasis text-uppercase">{{ menu.header }}</span>
            </v-list-subheader>
            <MenuItem v-for="(item, i) in menu.items.filter(checkRole)" :key="i" :item="item" class="leftPadding" />
          </template>
        </template>
        <!-- <Moreoption/> -->
      </v-list>
    </div>
  </v-navigation-drawer>

  <!-- App Header -->
  <div class="container verticalLayout">
    <div class="maxWidth">
      <v-app-bar elevation="0" height="70">
        <div class="d-flex align-center justify-space-between w-100 px-sm-5 px-4">
          <div>
            <v-btn class="hidden-lg-and-up text-muted" icon variant="flat" size="small" @click="sDrawer = !sDrawer">
              <v-icon icon="mdi-menu" size="20" stroke-width="1.5" />
            </v-btn>
          </div>
          <div>
            <!-- User Menu -->
            <v-menu :close-on-content-click="false">
              <template #activator="{ props }">
                <v-btn class="me-2" variant="text" v-bind="props" prepend-icon="mdi-account-circle"> {{ user?.name }} </v-btn>
              </template>
              <v-sheet rounded="xl" width="200" elevation="8" class="mt-2">
                <v-list class="py-4" lines="one" density="compact">
                  <!-- <v-list-item to="/company" color="primary" prepend-icon="mdi-domain" title="自社情報" class="pl-4 text-body-1">
                  </v-list-item> -->
                  <v-list-item to="/password" color="primary" prepend-icon="mdi-key" title="パスワード" class="pl-4 text-body-1">
                  </v-list-item>
                </v-list>
                <div class="pb-4 px-5 text-center">
                  <v-btn
                    prepend-icon="mdi-logout"
                    to=""
                    color="primary"
                    variant="outlined"
                    class="rounded-pill"
                    block
                    @click.prevent="logout"
                  >
                    ログアウト
                  </v-btn>
                </div>
              </v-sheet>
            </v-menu>
          </div>
        </div>
      </v-app-bar>
    </div>
  </div>

  <!-- Main -->
  <v-main>
    <v-container fluid class="page-wrapper bg-background px-sm-5 px-4 pt-8 rounded-xl">
      <div class="maxWidth">
        <slot></slot>
      </div>
    </v-container>
  </v-main>
</template>
