<script setup>
import headerLogo from '@/assets/img/headerLogo.png'
import { ref } from 'vue'
</script>

<template>
  <div class="flex items-center justify-between bg-black border-t-4 border-white p-8 h-28 z-10">
    <img class="w-auto h-24" :src="headerLogo" alt="TimeLock" />
    <h2 class="text-lg font-inter">
      Privacy Policy<br />
      ©2025 TimeLock All Rights Reserved
    </h2>
  </div>
</template>
