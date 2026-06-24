<script setup>
import { useAuthStore } from '@/stores/auth'
import AppHeader from '@/components/AppHeader.vue'

const auth = useAuthStore()
</script>

<template>
  <div>
    <AppHeader v-if="auth.isAuthenticated" />
    <router-view />
  </div>
</template>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #F5F4F0;
}
</style>
