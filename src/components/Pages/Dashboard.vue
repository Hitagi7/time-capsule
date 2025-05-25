<script setup>
import Header from '@/components/Dashboard/Header.vue'
import Tabs from '@/components/Dashboard/Tabs.vue'
import Message from '@/components/Dashboard/Message.vue'
</script>

<!-- Ibutang ang tabs ari nga component, para pag pass ug object dali ra sa message.vue ug create.vue -->
<template>
  <Header />
  <Message />
</template>
