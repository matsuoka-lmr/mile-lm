<template>
  <v-card elevation="10">
    <v-card-title class="d-flex align-center ma-4">
      <span class="font-weight-bold me-auto">パスワード変更</span>
    </v-card-title>
    <v-card-text>
      <v-form ref="form" @submit.prevent="save">
        <v-container>
          <v-row>
            <v-col cols="12" sm="6">
              <div class="ma-2">
                <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">会社名</label>
                <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ user?.company.name }}</div>
              </div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="ma-2">
                <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">ユーザータイプ</label>
                <div class="text-h6" style="padding-top: 14px; padding-left: 6px">
                  {{ user?.role == 99 ? 'システム管理者' : String(user?.role).endsWith('9') ? '管理者' : '一般ユーザー' }}
                </div>
              </div>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" sm="6">
              <div class="ma-2">
                <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">メールアドレス</label>
                <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ user?.email }}</div>
              </div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="ma-2">
                <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">名前</label>
                <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ user?.name }}</div>
              </div>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="password"
                label="現在のパスワード"
                variant="underlined"
                required
                class="ma-2"
                trim
                :type="visible1 ? 'text' : 'password'"
                prepend-inner-icon="mdi-lock-outline"
                :append-inner-icon="visible1 ? 'mdi-eye-off' : 'mdi-eye'"
                :rules="[(v: string) => !!v || '必須項目です']"
                @click:append-inner="visible1 = !visible1"
              />
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="newpass"
                label="新パスワード"
                placeholder="パスワード"
                variant="underlined"
                required
                class="ma-2"
                trim
                :type="visible2 ? 'text' : 'password'"
                prepend-inner-icon="mdi-lock-outline"
                :append-inner-icon="visible2 ? 'mdi-eye-off' : 'mdi-eye'"
                :rules="[
                  (v: string) => /^([a-zA-Z0-9]{8,})$/.test(v) || '半角英数字8桁以上が必要',
                  (v: string) => /.*[a-z]/.test(v) || '小文字一つ以上が必要',
                  (v: string) => /.*[A-Z]/.test(v) || '大文字一つ以上が必要',
                  (v: string) => /.*[0-9]/.test(v) || '数字一つ以上が必要'
                ]"
                @click:append-inner="visible2 = !visible2"
              />
            </v-col>
          </v-row>
          <v-row>
            <v-spacer></v-spacer>
            <v-btn
              color="primary"
              class="ma-2"
              prepend-icon="mdi-content-save"
              :disabled="saving || !dirty || !($refs.form as VForm).isValid"
              type="submit"
            >
              保存
            </v-btn>
            <v-btn prepend-icon="mdi-restore" class="ma-2" :disabled="!dirty" @click.prevent="reset"> リセット </v-btn>
          </v-row>
        </v-container>
      </v-form>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import { useApi } from '@/common/api'
import { useAuthStore } from '@/stores'
import { useToast } from 'vue-toastification'
import type { VForm } from 'vuetify/components'
const api = useApi()
const { user } = useAuthStore()
const password = ref('')
const newpass = ref('')
const visible1 = ref(false)
const visible2 = ref(false)
const saving = ref(false)
const dirty = computed(() => password.value != '' || newpass.value != '')
const reset = () => {
  password.value = ''
  newpass.value = ''
}
const save = () => {
  saving.value = true
  api
    .post<{ success?: boolean; errors?: string[] }>('password', { password: password.value, newpass: newpass.value })
    .then((ret) => {
      const toast = useToast()
      if (ret.success) {
        toast.success('正常に更新されました。')
        reset()
      } else if (Array.isArray(ret.errors) && ret.errors.length) toast.error(ret.errors[0])
      else toast.error('パスワード変更失敗しました。')
    })
    .finally(() => (saving.value = false))
}
</script>
