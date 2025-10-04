<template>
  <!-- Wrapper to maintain compatibility with routes using "admin/users/Index" -->
  <component :is="Component" />
</template>

<script setup lang="ts">
import Component from '@/pages/admin/Users.vue';
</script>

