<script setup>
import { ref, computed } from 'vue'
import mailIcon from '@/assets/icons/mailIcon.svg'
import passwordIcon from '@/assets/icons/passwordIcon.svg'

const props = defineProps(['overlayOptions', 'closeSignup', 'openLogin'])

// variable to control the visibility of the overlay using the passed object
const showOverlay = computed(() => props.overlayOptions.show)

const email = ref('')
const password = ref('')
const confirmPassword = ref('')

const handleSignup = () => {
  if (password.value !== confirmPassword.value) {
    alert('Passwords do not match')
    return
  }

  console.log('Sign Up with', { email: email.value, password: password.value })
  props.closeSignup
}
</script>

<template>
  <transition name="fade-slide">
    <div
      class="fixed h-screen inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      v-if="showOverlay"
    >
      <div class="bg-[#393939] rounded-xl overflow-hidden px-16 py-12">
        <div class="flex justify-between mb-8">
          <h2 class="w-96 text-white text-3xl font-bold font-['Inter']">Sign up to TimeLock</h2>
        </div>
        <form @submit.prevent="handleSignup">
          <div class="flex flex-col gap-6">
            <div class="flex flex-col gap-3">
              <label class="font-semibold font-['Inter']">Email</label>
              <div>
                <img class="absolute ml-4 mt-2.5" :src="mailIcon" alt="Mail Icon" />
                <input
                  type="email"
                  v-model="email"
                  class="w-full px-11 py-2 border rounded-lg text-black outline outline-2 outline-offset-[-1px] outline-slate-300"
                  placeholder="john_doe@email.com"
                  required
                />
              </div>
            </div>
            <div class="flex flex-col gap-3">
              <label class="font-semibold font-['Inter']">Password</label>
              <div>
                <img class="absolute ml-4 mt-2.5" :src="passwordIcon" alt="Password Icon" />
                <input
                  type="password"
                  v-model="password"
                  class="w-full px-11 py-2 border rounded-lg text-black outline outline-2 outline-offset-[-1px] outline-slate-300"
                  placeholder="Should be 8 characters and above"
                  required
                />
              </div>
            </div>
            <div class="flex flex-col gap-3">
              <label class="font-semibold font-['Inter']">Confirm Password</label>
              <div>
                <img class="absolute ml-4 mt-2.5" :src="passwordIcon" alt="Password Icon" />
                <input
                  type="password"
                  v-model="confirmPassword"
                  class="w-full px-11 py-2 border rounded-lg text-black outline outline-2 outline-offset-[-1px] outline-slate-300"
                  placeholder="Should be 8 characters and above"
                  required
                />
              </div>
            </div>
          </div>
          <div class="flex flex-col gap-6 mt-8">
            <button
              type="submit"
              class="shadow-xl py-6 h-20 bg-black rounded-xl inline-flex justify-center items-center text-white text-lg font-bold font-['Inter'] hover:opacity-80 hover:scale-105 transition-all duration-300 ease-in-out"
            >
              Create Account
            </button>
            <button
              @click="props.closeSignup"
              type="button"
              class="shadow-xl py-6 h-12 bg-white rounded-xl inline-flex justify-center items-center text-black text-lg font-bold font-['Inter'] hover:opacity-80 hover:scale-105 transition-all duration-300 ease-in-out"
            >
              Cancel
            </button>

            <p
              class="bg-inherit text-white text-sm font-normal font-['Inter'] text-center justify-start"
            >
              Already have an account?
              <span
                class="text-indigo-400 text-sm font-normal font-['Inter'] underline hover:cursor-pointer hover:text-indigo-500"
                @click="props.openLogin"
                >Log in.</span
              >
            </p>
          </div>
        </form>
      </div>
    </div>
  </transition>
</template>
