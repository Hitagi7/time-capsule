<script setup>
import { ref } from 'vue'
import headerLogo from '@/assets/img/headerLogo.png'
import LoginOverlay from '@/components/Landing/LoginOverlay.vue'
import SignupOverlay from '@/components/Landing/SignupOverlay.vue'

const overlayLoginOptions = ref({
  show: false,
})

const overlaySignupOptions = ref({
  show: false,
})

const openLoginOverlay = () => {
  overlaySignupOptions.value.show = false
  overlayLoginOptions.value.show = true
}

const openSignupOverlay = () => {
  overlayLoginOptions.value.show = false
  overlaySignupOptions.value.show = true
}

const closeLoginOverlay = () => {
  overlayLoginOptions.value.show = false
}

const closeSignupOverlay = () => {
  overlaySignupOptions.value.show = false
}
</script>

<template>
  <div
    class="w-auto top-0 flex items-center justify-between bg-black border-b-4 border-white p-8 h-28 z-10"
  >
    <img class="flex-none w-auto h-24" :src="headerLogo" alt="TimeLock" />
    <div class="flex items-center px-12 gap-8">
      <button
        class="uppercase w-32 h-12 bg-black font-['Inter'] font-semibold text-lg outline outline-1 rounded-lg hover:opacity-80 hover:scale-105 transition-all duration-300 ease-in-out"
        type="button"
        @click="openLoginOverlay"
      >
        Login
      </button>
      <button
        class="uppercase w-32 h-12 bg-white font-['Inter'] font-semibold text-black text-lg rounded-lg hover:opacity-80 hover:scale-105 transition-all duration-300 ease-in-out"
        type="button"
        @click="openSignupOverlay"
      >
        Sign Up
      </button>
    </div>
  </div>
  <LoginOverlay
    :overlayOptions="overlayLoginOptions"
    :closeLogin="closeLoginOverlay"
    :openSignup="openSignupOverlay"
  />
  <SignupOverlay
    :overlayOptions="overlaySignupOptions"
    :closeSignup="closeSignupOverlay"
    :openLogin="openLoginOverlay"
  />
</template>
