<template>
  <v-card elevation="10">
    <v-card-title class="d-flex align-center ma-4">
      <span class="font-weight-bold me-auto">{{ title }}{{ isEdit ? '詳細' : '作成' }}</span>
      <v-btn prepend-icon="mdi-arrow-left" variant="text" @click.prevent="backComfirm">
        戻る
        <v-dialog v-model="saveConfirmDialog" width="auto">
          <v-card>
            <v-card-text> 変更内容が保存されていません。よろしいでしょうか？ </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn variant="flat" color="success" @click.prevent="back">はい</v-btn>
              <v-btn variant="flat" color="error" @click.prevent="saveConfirmDialog = false"> いいえ </v-btn>
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
              <div class="ma-2">
                <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">会社名</label>
                <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ (item.company as Resource).name || '' }}</div>
              </div>
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
                  (v) => !v || /^([a-zA-Z0-9]{8,})$/.test(v) || '半角英数字8桁以上が必要',
                  (v) => !v || /.*[a-z]/.test(v) || '小文字一つ以上が必要',
                  (v) => !v || /.*[A-Z]/.test(v) || '大文字一つ以上が必要',
                  (v) => !v || /.*[0-9]/.test(v) || '数字一つ以上が必要'
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

          <v-sheet class="d-flex">
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
            <v-spacer></v-spacer>
            <v-btn
              v-if="isEdit"
              color="error"
              class="ma-2"
              prepend-icon="mdi-delete"
              :disabled="saving || dirty"
              @click.prevent="delConfirmDialog = true"
            >
              削除
            </v-btn>

            <v-dialog v-model="delConfirmDialog" width="auto">
              <v-card>
                <v-card-text> {{ title }}(ID:{{ item.id }}) を削除します。よろしいでしょうか？ </v-card-text>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn variant="flat" color="success" @click="del">はい</v-btn>
                  <v-btn variant="flat" color="error" @click="delConfirmDialog = false"> いいえ </v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-sheet>
        </v-container>
      </v-form>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import { useApi } from '@/common/api'
import type { ID, Resource } from '@/common/defines'
import type { VForm } from 'vuetify/components'
const title = 'ユーザー'
const route = useRoute('/user/[id]')
const router = useRouter()

const roles = [
  { title: '一般ユーザー', value: 1 },
  { title: '管理者', value: 9 }
]
const visible = ref(false)
const item = ref<Resource>({})
const isEdit = computed(() => !!item.value.id)
const org = ref(JSON.stringify(item.value))
const api = useApi()
const loading = ref(false)
const init = async () => {
  loading.value = true
  api
    .one('user', route.params.id)
    .then((result) => {
      result.role = Number(result.role) % 10
      org.value = JSON.stringify(result)
      item.value = result
      loading.value = false
    })
    .catch(() => {
      router.replace({ path: '/error/404' })
    })
}
init()

const dirty = computed(() => org.value != JSON.stringify(item.value))

const reset = () => {
  item.value = JSON.parse(org.value)
}

const saving = ref(false)
const save = () => {
  saving.value = true
  const saveFunc = item.value.id ? api.update : api.create
  saveFunc('user', item.value)
    .then((result) => {
      if (result.success) router.push('/user')
    })
    .catch(console.log)
    .finally(() => (saving.value = false))
}
const delConfirmDialog = ref(false)
const del = () => {
  saving.value = true
  api
    .del('user', item.value.id as ID)
    .then((result) => {
      if (result.success) router.push('/user')
    })
    .catch(console.log)
    .finally(() => (saving.value = false))
}
watch(
  () => route.params.id,
  async () => {
    init()
  }
)
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
