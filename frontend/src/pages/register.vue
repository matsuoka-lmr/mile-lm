<template>
  <v-row class="d-flex mb-3">
    <v-col cols="12">
      <v-label class="font-weight-medium mb-1">Name</v-label>
      <v-text-field variant="outlined" color="primary"></v-text-field>
    </v-col>
    <v-col cols="12">
      <v-label class="font-weight-medium mb-1">Email Address</v-label>
      <v-text-field variant="outlined" type="email" color="primary"></v-text-field>
    </v-col>
    <v-col cols="12">
      <v-label class="font-weight-medium mb-1">Password</v-label>
      <v-text-field variant="outlined" type="password" color="primary"></v-text-field>
    </v-col>
    <v-col cols="12">
      <v-btn to="/" color="primary" rounded="pill" size="large" block flat>利用申請登録</v-btn>
    </v-col>
  </v-row>
  <h6 class="text-subtitle-1 text-grey100 d-flex justify-center align-center mt-3">
    既にアカウントをお持ちですか？
    <v-btn variant="plain" to="/login" class="text-primary text-body-1 opacity-1 font-weight-medium pl-2">ログイン</v-btn>
  </h6>
</template>

<route lang="json">
{
  "meta": {
    "layout": "auth"
  }
}
</route>
