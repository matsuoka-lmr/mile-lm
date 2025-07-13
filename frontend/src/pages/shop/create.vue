<template>
  <v-card elevation="10">
    <v-card-title class="d-flex align-center ma-4">
      <span class="font-weight-bold me-auto">{{ title }}{{ isEdit ? '詳細' : '作成' }}</span>
      <v-btn prepend-icon="mdi-arrow-left" variant="text" @click.prevent="backComfirm">
        戻る
        <!-- <v-tooltip activator="parent" location="end">戻る</v-tooltip> -->
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
    <v-card-subtitle v-if="isEdit">
      <span class="me-auto">{{ title }}(ID: {{ item.id }})</span>
    </v-card-subtitle>
    <!-- <v-container>
      <v-row>
        <v-btn icon variant="flat" @click.prevent="backComfirm">
          <v-icon icon="mdi-arrow-left"></v-icon>
          <v-tooltip activator="parent" location="end">戻る</v-tooltip>
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
      </v-row>
    </v-container> -->
    <v-card-text v-if="loading" class="editor-message">
      <v-progress-circular indeterminate></v-progress-circular>
    </v-card-text>
    <v-card-text v-else>
      <!-- <v-card-title> {{ title }}{{ isEdit ? '詳細' : '作成' }} </v-card-title>
      <v-card-subtitle v-if="isEdit">
        <span class="me-auto">{{ title }}(ID: {{ item.id }})</span>
      </v-card-subtitle>
      <v-divider></v-divider> -->
      <v-form ref="form" @submit.prevent="save">
        <v-container>
          <v-row v-for="(row, i) in rows" :key="i">
            <template v-for="f in row" :key="f.key">
              <v-col v-if="f.editor == 'textarea'" cols="12">
                <v-textarea
                  v-model="item[f.key]"
                  :label="f.title"
                  :placeholder="f.title"
                  :rules="f.rules || []"
                  class="ma-2"
                  trim
                  rows="1"
                  auto-grow
                />
              </v-col>
              <v-col v-else cols="12" sm="6">
                <v-select
                  v-if="f.editor == 'select'"
                  v-model="item[f.key]"
                  clearable
                  :label="f.title"
                  :placeholder="f.title"
                  :items="f.options"
                  :rules="f.rules || []"
                >
                </v-select>
                <v-text-field
                  v-else
                  v-model="item[f.key]"
                  :label="f.title"
                  :placeholder="f.title"
                  :rules="f.rules || []"
                  class="ma-2"
                  :type="f.editor || 'text'"
                  trim
                />
              </v-col>
            </template>
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
          </v-sheet>
        </v-container>
      </v-form>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import { useApi } from '@/common/api'
import type { VForm } from 'vuetify/components'
import type { Field } from '@/common/defines'
const title = 'タイヤショップ'
const router = useRouter()
const fields: Field[] = [
  {
    key: 'name',
    title: '会社名',
    rules: [(v: string) => !!v || '必須項目です'],
    search: true
  },
  {
    key: 'phone',
    title: '電話番号',
    editor: 'tel',
    rules: [(v: string) => /^[0-9]*$/.test(v) || '半角数字のみを入力してください'],
    search: true
  },
  {
    key: 'address',
    title: '住所',
    editor: 'textarea'
  },
  {
    key: 'memo',
    title: '備考',
    editor: 'textarea',
    search: true
  },
  {
    key: 'oil_notice_days',
    title: 'タイヤ交換通知目安(日数)',
    editor: 'number'
  },
  {
    key: 'oil_notice_mileage',
    title: 'タイヤ交換通知目安(km)',
    editor: 'number'
  },
  {
    key: 'tire_notice_days',
    title: 'タイヤローテーション通知目安(日数)',
    editor: 'number'
  },
  {
    key: 'tire_notice_mileage',
    title: 'タイヤローテーション通知目安(km)',
    editor: 'number'
  },
  {
    key: 'battery_notice_days',
    title: '100Km点検通知目安(日数)',
    editor: 'number'
  },
  {
    key: 'battery_notice_mileage',
    title: '100Km点検通知目安(km)',
    editor: 'number'
  }
]
const editFields = fields.filter((f) => f.key != 'id' && f.edit !== false)
let oneRow: Field[] = []
const rows = [oneRow]
editFields.forEach((f, i) => {
  if (i && (f.row || f.editor == 'textarea')) {
    oneRow = [f]
    rows.push(oneRow)
  } else oneRow.push(f)
})
const item = ref(Object.fromEntries(new Map(editFields.map((f) => [f.key, '']))))
const isEdit = computed(() => !!item.value.id)
const org = JSON.stringify(item.value)
const api = useApi()
const loading = ref(false)

const dirty = computed(() => org != JSON.stringify(item.value))

const reset = () => {
  item.value = JSON.parse(org)
}

const saving = ref(false)
const save = () => {
  saving.value = true
  const saveFunc = item.value.id ? api.update : api.create
  saveFunc('shop', item.value)
    .then((result) => {
      if (result.success) router.push('/shop')
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
  router.push('/shop')
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
  roles: [1, 9]
</route>
