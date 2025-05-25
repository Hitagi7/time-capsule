<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import headerLogo from '@/assets/img/headerLogo.png'
import iconUser from '@/assets/icons/icon-user.svg'

const router = useRouter()
const dropdownOptions = ref({
  show: false,
})

const username = ref('P diddy')

const handleDropdown = () => {
  dropdownOptions.value.show = !dropdownOptions.value.show
}

const handleLogout = () => {
  // Clear user data from localStorage
  localStorage.removeItem('userEmail')
  // Redirect to landing page
  router.push('/')
}
</script>

<template>
  <div
    class="w-auto top-0 flex items-center justify-between bg-black border-b-4 border-white p-8 h-28 z-50"
  >
    <img class="w-auto h-24" :src="headerLogo" alt="TimeLock" />
    <div class="flex items-center gap-5 text-2xl font-semibold font-inter">
      <h3>{{ username }}</h3>
      <div
        class="w-16 h-16 relative bg-slate-100 rounded-[100px] outline outline-4 outline-neutral-600 overflow-hidden animated"
        @click="handleDropdown"
      >
        <div class="w-14 h-14 left-[5.25px] top-[3.94px] absolute overflow-hidden">
          <img class="left-[0.3px] absolute" :src="iconUser" alt="Default User" />
        </div>
      </div>
      <transition name="fade">
        <div
          class="fixed top-24 right-4 py-2 px-4 bg-white text-lg text-black outline outline-neutral-600 rounded-lg shadow-lg hover:cursor-pointer hover:opacity-80 z-50"
          v-if="dropdownOptions.show"
          @click="handleLogout"
        >
          Log out
        </div>
      </transition>
    </div>
  </div>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
  transition:
    opacity 0.3s ease,
    transform 0.3s ease;
}

.fade-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.fade-enter-to {
  opacity: 1;
  transform: translateY(0);
}

.fade-leave-from {
  opacity: 1;
  transform: translateY(0);
}

.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
