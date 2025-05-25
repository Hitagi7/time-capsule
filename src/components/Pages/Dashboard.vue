<script setup>
import Header from '@/components/Dashboard/Header.vue'
import Tabs from '@/components/Dashboard/Tabs.vue'
import Message from '@/components/Dashboard/Message.vue'
import Create from '@/components/Dashboard/Create.vue'
import { ref } from 'vue'
import { defineOptions } from 'vue'

defineOptions({
  name: 'UserDashboard',
})

const currentTab = ref('message')
</script>

<template>
  <Header />
  <Tabs v-model="currentTab" />
  <Message v-if="currentTab === 'message'" />
  <Create v-if="currentTab === 'create'" />
</template>
