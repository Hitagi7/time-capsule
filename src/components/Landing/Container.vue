<script setup>
import bigLogo from '@/assets/img/bigLogo.png'
import leftStar from '@/assets/img/left-star.png'
import rightStar from '@/assets/img/right-star.png'
import hourglass from '@/assets/img/hourglass.png'
import LoginOverlay from '@/components/Landing/LoginOverlay.vue'
import SignupOverlay from '@/components/Landing/SignupOverlay.vue'
import { ref } from 'vue'

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
  <div class="flex justify-between items-center mt-20">
    <img :src="leftStar" alt="Left Stars" class="h-[636px] self-start" />
    <div class="flex flex-col justify-center items-center text-center">
      <img :src="bigLogo" alt="TimeLock" />
      <p class="w-[876px] text-center">
        Welcome to <strong>TimeLock</strong>
        , where your words travel through time. Write a message today, set the date, and let it
        drift through the cosmos until it’s unlocked in the future. Capture a moment, share a
        secret, or send a wish to tomorrow. Your stories will wait, patiently suspended in time,
        ready to be discovered.
      </p>
    </div>
    <img :src="rightStar" alt="Right Stars" class="w-[420px] translate-y-40" />
  </div>
  <div class="flex justify-around items-center mt-52 mb-20">
    <img :src="hourglass" alt="Hourglass" />
    <div class="flex flex-col justify-around text-left gap-16">
      <p class="w-[785px] h-min">
        Every moment holds a story, a secret, a wish for the days yet to come. Time may pass, but
        your words don’t have to. Write a message, set the date, and let Timelock carry it forward.
      </p>
      <h1 class="text-2xl font-bold">THE FUTURE IS WAITING. WHAT WILL YOU SAY?</h1>
      <button
        class="w-80 h-12 py-6 bg-white rounded-xl inline-flex justify-center items-center gap-2.5 overflow-hidden text-black text-lg font-semibold font-['Inter'] hover:opacity-80 hover:scale-105 transition-all duration-300 ease-in-out"
        @click="openLoginOverlay"
      >
        WRITE YOUR MESSAGE
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

<style>
p,
h1 {
  @apply bg-black bg-opacity-70 p-1 text-white text-2xl font-normal font-['Space_Mono'] leading-10 tracking-wide;
}
</style>
