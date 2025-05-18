<script setup>
import { ref } from 'vue'

const tabs = ref('message')

const handleTabChange = (tab) => {
  tabs.value = tab
}
</script>

<template>
  <div
    class="flex justify-center py-12 text-2xl font-bold font-spaceMono leading-10 tracking-widest gap-1"
  >
    <button
      @click="handleTabChange('message')"
      class="w-36 h-14 border border-white animated"
      :class="
        tabs === 'message' ? 'text-black border-black bg-white' : 'text-white border-white bg-black'
      "
    >
      MESSAGE
    </button>
    <button
      @click="handleTabChange('create')"
      class="w-36 h-14 bg-black border border-white animated"
      :class="
        tabs === 'create' ? 'text-black border-black bg-white' : 'text-white border-white bg-black'
      "
    >
      CREATE
    </button>
  </div>
</template>
