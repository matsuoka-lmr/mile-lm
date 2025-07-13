<template>
  <v-card elevation="10">
    <v-card-title class="d-flex align-center ma-4">
      <span class="font-weight-bold me-auto">ユーザー作成</span>
      <v-btn prepend-icon="mdi-arrow-left" variant="text" @click.prevent="backComfirm">
        戻る
        <v-dialog v-model="saveConfirmDialog" width="auto">
          <v-card>
            <v-card-text> 変更内容が保存されていません。よろしいでしょうか？ </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn variant="flat" color="success" @click="back">はい</v-btn>
              <v-btn variant="flat" color="error" @click="saveConfirmDialog = false"> いいえ </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-btn>
    </v-card-title>
    <v-card-text v-if="loading" class="editor-message">
      <v-progress-circular indeterminate></v-progress-circular>
    </v-card-text>
    <v-card-text v-else>
      <v-form ref="form" @submit.prevent="save">
        <v-container>
          <v-row>
            <v-col cols="12" sm="6">
              <v-select
                v-model="item.company_id"
                label="会社"
                :items="companies"
                variant="underlined"
                class="ma-2"
                item-title="name"
                item-value="id"
              >
              </v-select>
            </v-col>
            <v-col cols="12" sm="6">
              <v-select v-model="item.role" label="権限" :items="roles" variant="underlined" class="ma-2"> </v-select>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="item.email"
                label="メールアドレス"
                placeholder="メールアドレス"
                variant="underlined"
                class="ma-2"
                type="email"
                required
                :rules="[(v) => !!v || '必須項目です']"
                trim
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="item.name"
                label="名前"
                placeholder="名前"
                variant="underlined"
                class="ma-2"
                :rules="[(v) => !!v || '必須項目です']"
                required
                trim
              />
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="item.password"
                label="パスワード"
                placeholder="パスワード"
                variant="underlined"
                class="ma-2"
                :rules="[
                  (v) => /^([a-zA-Z0-9]{8,})$/.test(v) || '半角英数字8桁以上が必要',
                  (v) => /.*[a-z]/.test(v) || '小文字一つ以上が必要',
                  (v) => /.*[A-Z]/.test(v) || '大文字一つ以上が必要',
                  (v) => /.*[0-9]/.test(v) || '数字一つ以上が必要'
                ]"
                required
                trim
                :type="visible ? 'text' : 'password'"
                :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
                @click:append-inner="visible = !visible"
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="item.phone"
                label="電話番号"
                placeholder="電話番号"
                variant="underlined"
                class="ma-2"
                type="tel"
                trim
              />
            </v-col>
          </v-row>

          <v-row>
            <v-btn
              color="primary"
              class="ma-2"
              prepend-icon="mdi-content-save"
              :disabled="saving || !dirty || !($refs['form'] as VForm).isValid"
              type="submit"
            >
              保存
            </v-btn>
            <v-btn prepend-icon="mdi-restore" class="ma-2" :disabled="!dirty" @click.prevent="reset"> リセット </v-btn>
            <v-spacer></v-spacer>
          </v-row>
        </v-container>
      </v-form>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import { useApi } from '@/common/api'
import type { ID, Resource } from '@/common/defines'
import { useAuthStore } from '@/stores'
import { useToast } from 'vue-toastification'
import type { VForm } from 'vuetify/components'
const router = useRouter()
const { user } = useAuthStore()
const companies = ref<Resource[]>([9, 19, 99].includes(user!.role) ? [user!.company] : [])

const roles = [
  { title: '一般ユーザー', value: 1 },
  { title: '管理者', value: 9 }
]
const visible = ref(false)
const item = ref<Resource>({
  role: 1,
  name: '',
  email: '',
  phone: '',
  password: ''
})
const api = useApi()
const loading = ref(false)

const loadCompanies = async () => {
  if ([1, 9, 99].includes(user!.role)) {
    loading.value = true
    const { data } = await api.all('shop')
    companies.value = companies.value.concat(data)
    loading.value = false
  }
  item.value.company_id = companies.value[0].id as ID
}
loadCompanies()

const dirty = computed(() => {
  const user = item.value
  return (
    user.company_id != companies.value[0].id ||
    user.role != 1 ||
    user.name != '' ||
    user.email != '' ||
    user.password != '' ||
    user.phone != ''
  )
})

const reset = () => {
  item.value = {
    company_id: companies.value[0].id,
    role: 1,
    name: '',
    email: '',
    password: '',
    phone: ''
  }
}

const saving = ref(false)
const save = () => {
  saving.value = true
  api
    .create('user', item.value)
    .then((result) => {
      if (result.success) router.push('/user')
      else if (result.errors) {
        useToast().error('失敗しました')
        console.log(result.errors)
      }
    })
    .catch(console.log)
    .finally(() => (saving.value = false))
}

const saveConfirmDialog = ref(false)
const backComfirm = () => {
  if (dirty.value) saveConfirmDialog.value = true
  else back()
}
const back = () => {
  saveConfirmDialog.value = false
  router.push('/user')
}
</script>

<style lang="scss" scoped>
.editor-message {
  text-align: center;
  align-items: center;
  padding: 4rem;
}
</style>

<route lang="yaml">
meta:
  roles: [1, 9, 19]
</route>
