<template>
  <v-form @submit.prevent="login">
    <v-row class="mb-3">
      <v-col cols="12">
        <v-text-field
          v-model="email"
          label="メールアドレス"
          type="email"
          variant="outlined"
          required
          hide-details
          color="primary"
          prepend-inner-icon="mdi-email-outline"
        />
      </v-col>
      <v-col cols="12">
        <v-text-field
          v-model="password"
          label="パスワード"
          variant="outlined"
          required
          hide-details
          :type="visible ? 'text' : 'password'"
          color="primary"
          prepend-inner-icon="mdi-lock-outline"
          :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
          @click:append-inner="visible = !visible"
        />
      </v-col>
      <v-col cols="12">
        <v-btn
          size="large"
          rounded="pill"
          color="primary"
          class="rounded-pill"
          block
          type="submit"
          flat
          :loading="sending"
          :disabled="sending"
        >
          ログイン
        </v-btn>
      </v-col>
    </v-row>
  </v-form>
</template>

<script lang="ts" setup>
import { useAuthStore } from '@/stores/auth'
const visible = ref(false)
const email = ref('')
const password = ref('')
const sending = ref(false)
const store = useAuthStore()
if (store.isLogined) store.logout()
const login = () => {
  sending.value = true
  store.login(email.value, password.value).finally(() => {
    sending.value = false
  })
}
</script>

<route lang="yaml">
meta:
  layout: auth
</route>
