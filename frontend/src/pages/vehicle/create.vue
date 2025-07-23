<template>
  <v-card elevation="10">
    <v-card-title class="d-flex align-center ma-4">
      <span class="font-weight-bold me-auto">車両情報作成</span>
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
      <v-form ref="vehicleCreate" @submit.prevent="save">
        <v-container>
          <v-row>
            <v-col v-if="[1, 9, 99].includes(user!.role)" cols="12" sm="6">
              <v-select
                v-model="shopId"
                label="ショップ"
                :items="shops"
                class="ma-2"
                item-title="name"
                item-value="id"
                required
                :rules="[(v) => !!v || '必須項目です']"
                @update:model-value="onShopChange"
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-select
                v-model="item.company_id"
                label="顧客"
                :items="customers.filter((c) => c.manage_company_id == shopId)"
                class="ma-2"
                item-title="name"
                item-value="id"
                required
                :rules="[(v) => !!v || '必須項目です']"
              >
              </v-select>
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
                :rules="[(v) => !!v || '必須項目です']"
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
                :rules="[(v) => !!v || '必須項目です']"
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
              <v-select
                v-model="item.user_id"
                label="担当者"
                :items="users.filter(u=>u.id==null||u.company_id==shopId)"
                class="ma-2"
                item-title="name"
                item-value="id"
              >
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
              <v-text-field
                v-model="item.oil_mileage"
                label="交換後走行距離(km)"
                placeholder="交換後走行距離"
                suffix="km"
                type="number"
                trim
              />
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
              <v-text-field
                v-model="item.tire_mileage"
                label="交換後走行距離(km)"
                placeholder="交換後走行距離"
                suffix="km"
                type="number"
                trim
              />
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
              <v-text-field
                v-model="item.battery_mileage"
                label="点検後走行距離(km)"
                placeholder="点検後走行距離"
                suffix="km"
                type="number"
                trim
              />
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

          <v-row class="ma-2">
            <v-btn
              color="primary"
              class="ma-2"
              prepend-icon="mdi-content-save"
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
const shops = ref<Resource[]>([])
const customers = ref<Resource[]>([])
const devices = ref<{ id: ID | null; title: ID }[]>([{ id: null, title: '[未装着]' }])
const users = ref<Resource[]>([])

const shopId = ref<ID>('')

const item = ref<Resource>({})
const api = useApi()
const loading = ref(true)

const onShopChange = async () => {
  loading.value = true
  if (!customers.value.length) {
    const { data } = await api.all('customer')
    customers.value = data
    shops.value = shops.value.filter((s) => data.find((c) => c.manage_company_id == s.id))
    shopId.value = shops.value.length ? (shops.value[0].id as ID) : data.length ? (data[0].manage_company_id as ID) : ''
  }
  item.value.company_id =
    shopId.value != '' && customers.value.length ? customers.value.filter((c) => c.manage_company_id == shopId.value)[0].id : ''

  devices.value = [
    { id: null, title: '[未装着]' },
    ...(await api.post<{ id: ID }[]>('device/unused', { company_id: shopId.value })).map(({ id }) => ({ id: id, title: id }))
  ]
  users.value = [
    { id: null, name: '[未指定]' },
    ...(await api.all('user', { search:{company_id: String(shopId.value) }})).data
  ]
  if (!users.value.find(u=>u.id==item.value.user_id && u.company_id==shopId.value)) item.value.user_id = null
  loading.value = false
}

const trimEmails = () => {
  if (item.value.emails) {
    item.value.emails = (item.value.emails as string)
      .split(',')
      .map((email) => email.trim())
      .join(',')
  }
}

const loadShops = async () => {
  if (!shops.value.length) {
    shops.value = [11, 19].includes(user!.role) ? [user!.company] : (await api.all('shop')).data
  }
  await onShopChange()
}

loadShops()
const dirty = computed(() => {
  const vehicle = item.value
  return (
    shopId.value != (shops.value.length ? shops.value[0].id : '') ||
    vehicle.company_id !=
      (shopId.value != '' && customers.value.length
        ? String(customers.value.filter((c) => c.manage_company_id == shopId.value)[0].id)
        : '') ||
    vehicle.model ||
    vehicle.number ||
    vehicle.device_id ||
    vehicle.user_id ||
    vehicle.emails ||
    vehicle.oil_date ||
    vehicle.oil_mileage ||
    vehicle.oil_notice_days ||
    vehicle.oil_notice_mileage ||
    vehicle.tire_date ||
    vehicle.tire_mileage ||
    vehicle.tire_notice_days ||
    vehicle.tire_notice_mileage ||
    vehicle.battery_date ||
    vehicle.battery_mileage ||
    vehicle.battery_notice_days ||
    vehicle.battery_notice_mileage
  )
})

const reset = () => {
  shopId.value = shops.value.length ? (shops.value[0].id as ID) : ''

  item.value = {
    company_id:
      shopId.value != '' && customers.value.length ? String(customers.value.filter((c) => c.manage_company_id == shopId.value)[0].id) : ''
  }
}

const saving = ref(false)
const save = () => {
  saving.value = true
  api
    .create('vehicle', item.value)
    .then((result) => {
      if (result.success) router.push('/vehicle')
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
