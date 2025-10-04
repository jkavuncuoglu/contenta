<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <Header />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
        <!-- Sidebar -->
        <UserSidebar />

        <!-- Main content -->
        <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
          <RouterView />
        </div>
      </div>
    </div>

    <!-- Footer -->
    <Footer />
  </div>
</template>

<script setup lang="ts">
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';
import UserSidebar from '@/components/UserSidebar.vue';
</script>
