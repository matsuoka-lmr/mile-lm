import { Config } from '../config'
import { useAuthStore } from '../stores/auth'
import router from '../router'
import { h } from 'vue'
import { useToast } from 'vue-toastification'
import dayjs from 'dayjs'
import {
  type AllRequestParams,
  type AllResponse,
  type ApiResponse,
  type CsvConfig,
  type StringMap,
  type Data,
  type ID,
  isSimple,
  type ListRequestParams,
  type ListResponse,
  type Resource
} from './defines'

interface API {
  get: <T>(url: string) => Promise<T>
  post: <T>(url: string, data: Data) => Promise<T>
  list: <T extends Resource>(res: string, params?: ListRequestParams) => Promise<ListResponse<T>>
  all: <T extends Resource>(res: string, params?: AllRequestParams) => Promise<AllResponse<T>>
  create: <T extends Resource>(res: string, data: T) => Promise<ApiResponse>
  one: <T extends Resource>(res: string, id: ID) => Promise<T>
  update: (res: string, data: Resource) => Promise<ApiResponse>
  del: (res: string, id: ID) => Promise<ApiResponse>
  csv: (res: string, config: CsvConfig, params?: AllRequestParams) => Promise<void>
  test: () => string | number
}

const createApi = (): API => {
  const abort = new AbortController()
  const toast = useToast()

  function removeEmpty(obj: Data): Data {
    if (isSimple(obj)) return obj
    if (Array.isArray(obj)) return obj.map((v) => removeEmpty(v)).filter((v) => v != null)
    const newObj: Data = {}
    Object.entries(obj).forEach(([k, v]) => {
      if (v != null) {
        if (isSimple(v)) newObj[k] = v
        else newObj[k] = removeEmpty(v)
      }
    })
    return newObj
  }

  const download = (filename: string, blob: Blob) => {
    const winurl = window.URL || window.webkitURL
    const url = winurl.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    link.click()
    winurl.revokeObjectURL(url)
  }

  const baseURL = Config.API
  const request = function (path: string, method: string, params: Data = null, headers: Record<string, string> = {}) {
    headers.Accept = 'application/json'
    const options: RequestInit = {
      method,
      headers,
      signal: abort.signal
    }
    const token = localStorage.getItem(Config.StoreKeyToken)
    if (token) {
      headers.Authorization = 'Bearer ' + token
    }
    if (params) {
      headers['Content-Type'] = 'application/json'
      options.body = JSON.stringify(removeEmpty(params))
    }
    return fetch(`${baseURL}/${path}`, options)
      .then(function (resp) {
        if (resp.ok) {
          if (resp.headers.has('x-token')) {
            localStorage.setItem(Config.StoreKeyToken, resp.headers.get('x-token') || '')
            useAuthStore().updatedAt = Date.now()
          }
          return resp.json()
        }

        // auth error
        if (401 == resp.status) {
          useAuthStore().logout()
          throw path == 'login' && options.method == 'POST' ? 'ログインに失敗しました' : '再度ログインしてください'
        }

        if (403 == resp.status) {
          useAuthStore().logout()
          throw 'Forbidden'
        }

        // Not found
        if (404 == resp.status) {
          router.replace('/error/404')
          throw 'Not found'
        }

        // Server Error
        if (500 == resp.status) {
          // router.replace({name:'err', params:{code: 500}})
          throw 'Server Error'
        }

        // Unprocessable Entity
        if (422 == resp.status) {
          return resp.json().then((data) => ({
            errors: data
          }))
        }

        const errors: string[] = []
        resp
          .json()
          .then((data) => {
            if (data.message) errors.push(String(data.message))
            for (const i in data.errors) errors.push(String(data.errors[i]))
          })
          .catch(() => errors.push(String(resp)))

        return Promise.reject(errors)
      })
      .catch((reason) => {
        const errors = []
        if (typeof reason === 'string') errors.push(reason)
        else if (Array.isArray(reason)) for (const i in reason) errors.push(String(reason[i]))
        else {
          if (Config.Debug) console.log(reason)
          if (reason.name != 'AbortError') abort.abort('通信エラー')
        }

        if (errors.length)
          toast.error(
            errors.length == 1
              ? errors[0]
              : h(
                  'ul',
                  {},
                  errors.map((err) => h('li', {}, err))
                )
          )
        return Promise.reject(errors)
      })
  }

  const api = {
    get: (url: string) => request(url, 'GET'),
    post: (url: string, data: Data) => request(url, 'POST', data),
    list: (res: string, params: ListRequestParams = { page: 1, perPage: 10 }) => request(res, 'POST', params),
    all: (res: string, params: AllRequestParams = {}) => request(res, 'POST', params),
    create: <T extends Resource>(res: string, data: T) =>
      request(`${res}`, 'PUT', data).then((ret) => {
        if (ret.success) toast.success('正常に登録されました。')
        return ret
      }),
    one: (res: string, id: ID) => request(`${res}/${id}`, 'GET'),
    update: (res: string, data: Resource) =>
      request(`${res}/${data.id}`, 'PATCH', data).then((ret) => {
        if (ret.success) toast.success('正常に更新されました。')
        return ret
      }),
    del: (res: string, id: ID) =>
      request(`${res}/${id}`, 'DELETE').then((ret) => {
        if (ret) toast.success('正常に削除されました。')
        return ret
      }),
    csv: (res: string, config: CsvConfig, params: AllRequestParams = {}) =>
      request(res, 'POST', params).then((data: { data: StringMap[] }) => {
        const filename = `${config.filename || res}_${dayjs().format('YYYYMMDD_HHmmss')}.csv`
        const headers = config.fields.map((field) => field.header)
        const list = data.data.map((rowData, i, list) =>
          config.fields.map((field) => (typeof field.value == 'function' ? field.value(rowData, i, list) : rowData[field.value])).join(',')
        )
        const content = [new Uint8Array([0xef, 0xbb, 0xbf]), headers.join(','), '\r\n', list.join('\r\n')]
        download(filename, new Blob(content, { type: 'text/csv' }))
      }),
    test: () => toast.success('test')
  }
  return api
}
let api: API | null = null
export const useApi = () => (api = api || createApi())
