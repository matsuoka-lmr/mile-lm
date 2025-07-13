<template>
  <v-card elevation="10">
    <v-card-title class="d-flex align-center ma-4">
      <span class="font-weight-bold me-auto">{{ title }}詳細</span>
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

    <v-form ref="form" @submit.prevent="save">
      <v-card-text v-if="loading" class="editor-message">
        <v-progress-circular indeterminate></v-progress-circular>
      </v-card-text>
      <v-card-text v-else>
        <v-container>
          <v-row>
            <v-col cols="12" sm="6">
              <div class="ma-2">
                <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">デバイスID</label>
                <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ item.id }}</div>
              </div>
            </v-col>
            <v-col cols="12" sm="6">
              <v-select
                v-model="item.company_id"
                label="ショップ"
                :items="shops"
                class="ma-2"
                item-title="name"
                item-value="id"
                variant="outlined"
                hide-details
              >
              </v-select>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="item.name"
                label="デバイス名"
                placeholder="デバイス名"
                class="ma-2"
                required
                :rules="[(v: string) => !!v || '必須項目です']"
                trim
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="item.measure_interval"
                :min="item.minimal_interval"
                label="測位間隔(秒)"
                placeholder="測位間隔(秒)"
                class="ma-2"
                type="number"
                required
                :rules="[(v: number) => !!v || '必須項目です']"
                trim
              />
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="6">
              <div class="ma-2">
                <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">最後位置日時</label>
                <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ item.last_loc_at }}</div>
              </div>
            </v-col>
            <v-col cols="12" sm="3">
              <div class="ma-2">
                <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">LAT</label>
                <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ item.lat }}</div>
              </div>
            </v-col>
            <v-col cols="12" sm="3">
              <div class="ma-2">
                <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">LNG</label>
                <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ item.lng }}</div>
              </div>
            </v-col>
          </v-row>

          <!-- <v-row>
            <v-col cols="12" sm="12">
              <div class="ma-2">
                <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">最終位置</label>
                <div v-if="address == ''" class="text-h6" style="padding-top: 14px; padding-left: 6px">
                  <v-progress-linear indeterminate></v-progress-linear>
                </div>
                <div v-else class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ address }}</div>
              </div>
            </v-col>
          </v-row> -->

          <v-row>
            <v-col cols="12" sm="6">
              <div class="ma-2">
                <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">デバイス詳細同期日時</label>
                <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ item.updated_at }}</div>
              </div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="ma-2">
                <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">位置履歴同期日時</label>
                <div class="text-h6" style="padding-top: 14px; padding-left: 6px">{{ item.last_measure_at }}</div>
              </div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="ma-2">
                <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">バッテリー</label>
                <div class="text-h6" style="padding-top: 14px; padding-left: 6px">
                  <v-progress-linear
                    :model-value="item.battery as number"
                    :color="(item.battery as number) < 10 ? 'error' : (item.battery as number) < 50 ? 'warning' : 'success'"
                    bg-color="grey200"
                    height="24"
                  >
                    <strong :style="{ color: (item.battery as number) < 10 ? 'red' : 'white' }">{{ item.battery }}%</strong>
                  </v-progress-linear>
                </div>
              </div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="ma-2">
                <label class="v-label v-field-label v-field-label--floating" style="visibility: visible">ステータス</label>
                <div class="text-h6" style="padding-top: 14px; padding-left: 6px">
                  <v-chip
                    :color="item.status == 'active' ? 'success' : item.status == 'deleted' ? 'error' : 'warning'"
                    :text="item.status as string"
                    class="text-uppercase"
                    size="small"
                    label
                  ></v-chip>
                </div>
              </div>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="12">
              <v-divider></v-divider>
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
          </v-sheet>
        </v-container>
      </v-card-text>
    </v-form>
  </v-card>
</template>

<script setup lang="ts">
import { useApi } from '@/common/api'
import { type Resource } from '@/common/defines'
import type { VForm } from 'vuetify/components'
const title = 'デバイス'
const route = useRoute('/device/[id]')
const router = useRouter()

const item = ref<Resource>({})
const org = ref('')
const api = useApi()
const loading = ref(true)
const shops = ref<Resource[]>([])
const address = ref('')

const init = async () => {
  loading.value = true
  try {
    const shopList: Resource[] = [{ id: 'null', name: '[未指定]' }]
    shopList.push(...(await api.all('shop')).data)
    shops.value = shopList
    const result = await api.one('device', route.params.id)
    if (!result.company_id) result.company_id = 'null'
    result.measure_interval = Number(result.measure_interval) || null
    org.value = JSON.stringify(result)
    item.value = result
    // api.post<{ address: string }>('address', { lat: result.lat, lng: result.lng }).then((ret) => (address.value = ret.address))
  } catch (e) {
    console.log(e)
    router.replace({ path: '/error/404' })
  } finally {
    loading.value = false
  }
}
init()

const dirty = computed(() => {
  return org.value != JSON.stringify(item.value)
})

const reset = () => {
  item.value = JSON.parse(org.value)
}

const saving = ref(false)
const save = () => {
  saving.value = true
  api
    .update('device', item.value)
    .then((result) => {
      if (result.success) router.push('/device')
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
  router.push('/device')
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
