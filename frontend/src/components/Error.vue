<script setup lang="ts">
type ErrorCode = '403' | '404' | '500'
const { code } = defineProps<{ code: ErrorCode }>()

type ErrorMessage = {
  title: string
  text: string
}
type ErrorMessages = {
  [key in ErrorCode]: ErrorMessage
}

const errors: ErrorMessages = {
  '403': {
    title: 'アクセスが拒否されました。',
    text: 'アクセスいただいたページは権限変更等原因で、現在利用できない可能性があります。'
  },
  '404': {
    title: 'お探しのページは見つかりません。',
    text: 'アクセスいただいたページは削除、変更されたか、現在利用できない可能性があります。'
  },
  '500': {
    title: 'サーバーエラー。',
    text: '申し訳ございません、サーバーエラーが発生し、現在利用できない可能性があります。'
  }
}

const error = (): ErrorMessage => errors[code] || errors['404']
</script>

<template>
  <v-empty-state
    :headline="code"
    :title="error().title"
    :text="error().text"
    text-width="1000"
    icon="mdi-alert"
    action-text="トップページへ戻る"
    @click:action="() => $router.push({ path: '/' })"
  />
</template>
