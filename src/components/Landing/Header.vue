<script setup>
import headerLogo from '@/assets/img/headerLogo.png'
import { ref } from 'vue'
</script>

<template>
  <div
    class="w-auto top-0 flex items-center justify-between bg-black border-b-4 border-white p-8 h-28 z-10"
  >
    <img class="flex-none w-auto h-24" :src="headerLogo" alt="TimeLock" />
    <div class="flex items-center px-12 gap-8">
      <button
        class="uppercase w-32 h-12 bg-black font-['Inter'] font-semibold text-lg outline outline-1 rounded-lg hover:bg-gray-900 hover:scale-105 transition-all duration-300 ease-in-out"
        type="button"
      >
        Login
      </button>
      <button
        class="uppercase w-32 h-12 bg-white font-['Inter'] font-semibold text-black text-lg rounded-lg hover:bg-gray-200 hover:scale-105 transition-all duration-300 ease-in-out"
        type="button"
      >
        Sign Up
      </button>
    </div>
  </div>
</template>
