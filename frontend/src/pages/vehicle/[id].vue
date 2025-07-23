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
      <v-container v-if="detail">
        <v-row>
          <v-col cols="12" sm="6">
            <div class="ma-2">
              <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">会社名</label>
              <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ customer.name || '-' }}</div>
            </div>
          </v-col>
        </v-row>

        <v-row>
          <v-col cols="12" sm="6">
            <div class="ma-2">
              <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">車種</label>
              <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ item.model || '-' }}</div>
            </div>
          </v-col>
          <v-col cols="12" sm="6">
            <div class="ma-2">
              <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">車両ナンバー</label>
              <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ item.number || '-' }}</div>
            </div>
          </v-col>
        </v-row>

        <v-row>
          <v-col cols="12" sm="6">
            <div class="ma-2">
              <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">GPS端末</label>
              <div class="text-h6" style="padding-top: 14px; padding-left: 6px">
                {{ item.device_id || '[未装着]' }}
              </div>
            </div>
          </v-col>
          <v-col cols="12" sm="6">
            <div class="ma-2">
              <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">車検日</label>
              <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ item.inspection_date || '-' }}</div>
            </div>
          </v-col>
        </v-row>

        <v-row>
          <v-col cols="12" sm="6">
            <div class="ma-2">
              <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">担当者</label>
              <div class="text-h6" style="padding-top: 14px; padding-left: 6px">
                {{ users.find(u=>u.id && u.id==item.user_id)?.name || '-' }}
              </div>
            </div>
          </v-col>
          <v-col cols="12" sm="6">
            <div class="ma-2">
              <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">通知メールアドレス</label>
              <div class="text-h6" style="padding-top: 14px; padding-left: 6px">
                <template v-if="emails.length">
                  <v-chip v-for="(email, i) in emails" :key="i" class="ma-1">
                    {{ email }}
                  </v-chip>
                </template>
                <template v-else>-</template>
              </div>
            </div>
          </v-col>
        </v-row>
        <v-divider></v-divider>
        <div class="text-caption mb-2">タイヤ交換設定</div>
        <v-row>
          <v-col cols="12" sm="6" md="4">
            <div class="ma-2">
              <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">交換日</label>
              <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ item.oil_date || '-' }}</div>
            </div>
          </v-col>
          <v-col cols="12" sm="6" md="4">
            <div class="ma-2">
              <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">交換後走行距離</label>
              <div class="text-h6" style="padding-top: 14px; padding-left: 6px">
                {{ item.oil_mileage ? `${item.oil_mileage} km` : '-' }}
              </div>
            </div>
          </v-col>
          <v-col cols="12" sm="6" md="4">
            <div class="ma-2">
              <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">通知目安</label>
              <div class="text-h6" style="padding-top: 14px; padding-left: 6px">
                {{ item.oil_notice_days ? `${item.oil_notice_days} 日` : '-' }} /
                {{ item.oil_notice_mileage ? `${item.oil_notice_mileage} km` : '-' }}
              </div>
            </div>
          </v-col>
        </v-row>
        <v-divider></v-divider>
        <div class="text-caption mb-2">タイヤローテーション設定</div>
        <v-row>
          <v-col cols="12" sm="6" md="4">
            <div class="ma-2">
              <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">交換日</label>
              <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ item.tire_date || '-' }}</div>
            </div>
          </v-col>
          <v-col cols="12" sm="6" md="4">
            <div class="ma-2">
              <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">交換後走行距離</label>
              <div class="text-h6" style="padding-top: 14px; padding-left: 6px">
                {{ item.tire_mileage ? `${item.tire_mileage} km` : '-' }}
              </div>
            </div>
          </v-col>
          <v-col cols="12" sm="6" md="4">
            <div class="ma-2">
              <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">通知目安</label>
              <div class="text-h6" style="padding-top: 14px; padding-left: 6px">
                {{ item.tire_notice_days ? `${item.tire_notice_days} 日` : '-' }} /
                {{ item.tire_notice_mileage ? `${item.tire_notice_mileage} km` : '-' }}
              </div>
            </div>
          </v-col>
        </v-row>
        <v-divider></v-divider>
        <div class="text-caption mb-2">100Km点検設定</div>
        <v-row>
          <v-col cols="12" sm="6" md="4">
            <div class="ma-2">
              <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">点検日</label>
              <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ item.battery_date || '-' }}</div>
            </div>
          </v-col>
          <v-col cols="12" sm="6" md="4">
            <div class="ma-2">
              <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">点検後走行距離</label>
              <div class="text-h6" style="padding-top: 14px; padding-left: 6px">
                {{ item.battery_mileage ? `${item.battery_mileage} km` : '-' }}
              </div>
            </div>
          </v-col>
          <v-col cols="12" sm="6" md="4">
            <div class="ma-2">
              <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">通知目安</label>
              <div class="text-h6" style="padding-top: 14px; padding-left: 6px">
                {{ item.battery_notice_days ? `${item.battery_notice_days} 日` : '-' }} /
                {{ item.battery_notice_mileage ? `${item.battery_notice_mileage} km` : '-' }}
              </div>
            </div>
          </v-col>
        </v-row>

        <v-sheet class="d-flex">
          <v-btn color="primary" class="ma-2" prepend-icon="mdi-pencil" @click.prevent="edit"> 編集 </v-btn>
          <v-spacer></v-spacer>
          <v-btn v-if="isEdit" color="error" class="ma-2" prepend-icon="mdi-delete" @click.prevent="delConfirmDialog = true"> 削除 </v-btn>

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

      <v-form v-else ref="form" @submit.prevent="save">
        <v-container>
          <v-row>
            <v-col cols="12" sm="6">
              <div class="ma-2">
                <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">会社名</label>
                <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ customer.name }}</div>
              </div>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="item.model"
                label="車種"
                placeholder="車種"
                class="ma-2"
                required
                :rules="[(v: string) => !!v || '必須項目です']"
                trim
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="item.number"
                label="車両ナンバー"
                placeholder="車両ナンバー"
                class="ma-2"
                required
                :rules="[(v: string) => !!v || '必須項目です']"
                trim
              />
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="6">
              <v-select v-model="item.device_id" label="GPS端末" :items="devices" class="ma-2" item-title="title" item-value="id">
              </v-select>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field v-model="item.inspection_date" label="車検日" placeholder="車検日" class="ma-2" type="date" trim />
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="6">
              <v-select v-model="item.user_id" label="担当者" :items="users" class="ma-2" item-title="name" item-value="id">
              </v-select>
            </v-col>
            <v-col cols="12" sm="6">
              <v-textarea
                v-model="item.emails"
                label="通知メールアドレス(カンマ区切り)"
                placeholder="通知メールアドレス(カンマ区切り)"
                class="ma-2"
                rows="1"
                auto-grow
                trim
                @blur="trimEmails"
              />
            </v-col>
          </v-row>

          <v-divider></v-divider>
          <div class="text-caption mb-2">タイヤ交換設定</div>
          <v-row>
            <v-col cols="12" sm="6" md="3">
              <v-text-field v-model="item.oil_date" label="交換日" placeholder="交換日" type="date" trim />
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-text-field v-model="item.oil_mileage" label="交換後走行距離(km)" placeholder="交換後走行距離" suffix="km" trim />
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-text-field v-model="item.oil_notice_days" label="通知目安(日数)" placeholder="通知目安" suffix="日" type="number" trim />
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-text-field v-model="item.oil_notice_mileage" label="通知目安(km)" placeholder="通知目安" suffix="km" type="number" trim />
            </v-col>
          </v-row>

          <v-divider></v-divider>
          <div class="text-caption mb-2">タイヤローテーション設定</div>
          <v-row>
            <v-col cols="12" sm="6" md="3">
              <v-text-field v-model="item.tire_date" label="交換日" placeholder="交換日" type="date" trim />
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-text-field v-model="item.tire_mileage" label="交換後走行距離(km)" placeholder="交換後走行距離" suffix="km" trim />
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-text-field v-model="item.tire_notice_days" label="通知目安(日数)" placeholder="通知目安" suffix="日" type="number" trim />
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-text-field v-model="item.tire_notice_mileage" label="通知目安(km)" placeholder="通知目安" suffix="km" type="number" trim />
            </v-col>
          </v-row>

          <v-divider></v-divider>
          <div class="text-caption mb-2">100Km点検設定</div>
          <v-row>
            <v-col cols="12" sm="6" md="3">
              <v-text-field v-model="item.battery_date" label="点検日" placeholder="点検日" type="date" trim />
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-text-field v-model="item.battery_mileage" label="点検後走行距離(km)" placeholder="点検後走行距離" suffix="km" trim />
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-text-field
                v-model="item.battery_notice_days"
                label="通知目安(日数)"
                placeholder="通知目安"
                suffix="日"
                type="number"
                trim
              />
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-text-field
                v-model="item.battery_notice_mileage"
                label="通知目安(km)"
                placeholder="通知目安"
                suffix="km"
                type="number"
                trim
              />
            </v-col>
          </v-row>

          <v-sheet class="d-flex">
            <v-btn
              color="primary"
              class="ma-2"
              prepend-icon="mdi-content-save"
              :disabled="saving || !dirty || !($refs.form && ($refs.form as VForm).isValid)"
              type="submit"
            >
              保存
            </v-btn>
            <v-btn prepend-icon="mdi-restore" class="ma-2" :disabled="!dirty" @click.prevent="reset"> リセット </v-btn>
            <v-spacer></v-spacer>
            <v-btn v-if="isEdit" color="error" class="ma-2" prepend-icon="mdi-cancel" :disabled="saving" @click.prevent="cancel">
              キャンセル
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
const title = '車両'
const route = useRoute('/vehicle/[id]')
const router = useRouter()
const detail = ref(true)
const item = ref<Resource>({})

const customer = ref<Resource>({})
const devices = ref<{ id: ID | null; title: ID }[]>([])
const users = ref<Resource[]>([])
const emails = ref<string[]>([])

const trimEmails = () => {
  if (item.value.emails) {
    emails.value = (item.value.emails as string).split(',').map((email) => email.trim())
    item.value.emails = emails.value.join(',')
  }
}

const isEdit = computed(() => !!item.value.id)
const org = ref(JSON.stringify(item.value))
const api = useApi()
const loading = ref(false)
const init = async () => {
  loading.value = true
  try {
    const result = await api.one('vehicle', route.params.id)
    if (result.oil_mileage) result.oil_mileage = Math.floor(Number(result.oil_mileage) / 1000)
    if (result.tire_mileage) result.tire_mileage = Math.floor(Number(result.tire_mileage) / 1000)
    if (result.battery_mileage) result.battery_mileage = Math.floor(Number(result.battery_mileage) / 1000)
    item.value = result
    trimEmails()
    org.value = JSON.stringify(result)
    customer.value = await api.one('customer', result.company_id as ID)
    devices.value = [{ id: null, title: '[未装着]' }]
    if (result.device_id) devices.value.push({ id: result.device_id as ID, title: result.device_id as ID })
    devices.value.push(
      ...(
        await api.post<{ id: ID }[]>('device/unused', {
          company_id: customer.value.manage_company_id as ID
        })
      )
        .filter(({ id }) => id != result.device_id)
        .map(({ id }) => ({ id: id, title: id }))
    )
    users.value = [
      { id: null, name: '[未指定]' },
      ...(await api.all('user', {search:{company_id:customer.value.manage_company_id as string}})).data.filter((u)=>u.company_id == customer.value.manage_company_id)
    ]
  } catch {
    router.replace({ path: '/error/404' })
  } finally {
    loading.value = false
  }
}
init()

const dirty = computed(() => org.value != JSON.stringify(item.value))
const edit = () => {
  org.value = JSON.stringify(item.value)
  detail.value = false
}
const cancel = () => {
  reset()
  detail.value = true
}
const reset = () => {
  item.value = JSON.parse(org.value)
}

const saving = ref(false)
const save = () => {
  saving.value = true
  trimEmails()
  api
    .update('vehicle', item.value)
    .then((result) => {
      if (result.success) {
        org.value = JSON.stringify(item.value)
        detail.value = true
      }
    })
    .catch(console.log)
    .finally(() => (saving.value = false))
}
const delConfirmDialog = ref(false)
const del = () => {
  saving.value = true
  api
    .del('vehicle', item.value.id as ID)
    .then((result) => {
      if (result.success) router.push('/vehicle')
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
  router.push('/vehicle')
}
</script>

<style lang="scss" scoped>
.editor-message {
  text-align: center;
  align-items: center;
  padding: 4rem;
}
</style>
