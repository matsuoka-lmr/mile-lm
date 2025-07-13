<template>
  <v-empty-state title="ご利用いただきありがとうございます！">
    <template #media>
      <img src="https://mamo-l.jp/source/img/logo.png" />
    </template>
    <template #headline>
      <div class="text-pre-wrap">{{ Config.AppName }}</div>
    </template>
    <v-container>
      <v-row>
        <v-col v-if="hasPermission([1, 9])" cols="12" md="6">
          <v-card
            title="ショップ管理"
            text="タイヤショップ情報を確認・管理します"
            to="shop"
            prepend-icon="mdi-car-wrench"
            class="elevation-10"
          ></v-card>
        </v-col>

        <v-col v-if="hasPermission([1, 9, 19])" cols="12" md="6">
          <v-card
            title="ユーザー管理"
            text="システム利用者情報を確認・管理します"
            to="user"
            prepend-icon="mdi-account-group"
            class="elevation-10"
          ></v-card>
        </v-col>

        <v-col v-if="hasPermission([1, 9])" cols="12" md="6">
          <v-card
            title="デバイス管理"
            text="GPS端末を情報を確認・管理します"
            to="device"
            prepend-icon="mdi-map-marker-multiple"
            class="elevation-10"
          ></v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card
            title="顧客管理"
            text="利用顧客情報を確認・管理します"
            to="customer"
            prepend-icon="mdi-account-tie"
            class="elevation-10"
          ></v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card
            title="車両管理"
            text="利用車両情報を確認・管理します"
            to="vehicle"
            prepend-icon="mdi-truck"
            class="elevation-10"
          ></v-card>
        </v-col>
      </v-row>
    </v-container>
  </v-empty-state>
</template>

<script lang="ts" setup>
import { Config } from '@/config'
import { useAuthStore } from '@/stores'

const { user } = useAuthStore()
const hasPermission = (roles: number[]) => user && (user.role == 99 || roles.includes(user.role))
</script>
