<template>
  <v-main>
    <div class="authentication">
      <v-container fluid class="pa-3">
        <v-row class="h-100vh d-flex justify-center align-center">
          <v-col cols="12" class="d-flex align-center">
            <div class="boxed-auth-wrap">
              <v-card rounded="xl" elevation="10" class="px-sm-1 px-0 mx-auto index-2" max-width="450">
                <v-card-item class="pa-sm-8">
                  <div class="d-flex justify-center mb-5">
                    <div class="logo">
                      <RouterLink to="/">
                        <img src="https://mamo-l.jp/source/img/logo.png" alt="home" />
                      </RouterLink>
                    </div>
                  </div>
                  <div class="d-flex align-center text-center mb-12">
                    <div class="text-h4 w-100 position-relative text-pre-wrap">
                      {{ Config.AppName }}
                    </div>
                  </div>
                  <router-view />
                </v-card-item>
              </v-card>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </div>
  </v-main>
</template>

<script setup lang="ts">
import { Config } from '@/config'
</script>
