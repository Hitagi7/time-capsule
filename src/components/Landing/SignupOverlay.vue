<script setup>
import { ref, computed } from 'vue'
import mailIcon from '@/assets/icons/mailIcon.svg'
import passwordIcon from '@/assets/icons/passwordIcon.svg'

const props = defineProps(['overlayOptions', 'closeSignup', 'openLogin'])

const showOverlay = computed(() => props.overlayOptions.show)

const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const errorMsg = ref('')
const successMsg = ref('')
const isLoading = ref(false)

const handleSignup = async () => {
  errorMsg.value = ''
  successMsg.value = ''

  // Validate form inputs
  if (!email.value || !password.value) {
    errorMsg.value = 'Please enter both email and password'
    return
  }

  if (password.value !== confirmPassword.value) {
    errorMsg.value = 'Passwords do not match'
    return
  }

  if (password.value.length < 8) {
    errorMsg.value = 'Password must be at least 8 characters'
    return
  }

  isLoading.value = true

  try {
    const res = await fetch('http://localhost/time-capsule/src/backend/signup.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        email: email.value,
        password: password.value,
        confirmPassword: confirmPassword.value,
      }),
    })

    let data
    try {
      data = await res.json()
    } catch (jsonErr) {
      console.error('JSON parsing error:', jsonErr)
      errorMsg.value = 'Invalid server response. Please try again.'
      isLoading.value = false
      return
    }

    if (!res.ok) {
      errorMsg.value = data?.error || 'Server error. Please try again later.'
      isLoading.value = false
      return
    }

    // Success case - server returned OK status
    successMsg.value = data.message || 'Account created successfully!'
    setTimeout(() => {
      props.closeSignup()
      props.openLogin()
    }, 1200)
  } catch (e) {
    console.error('Fetch error:', e)
    errorMsg.value = 'Unable to connect to the server. Please check your connection.'
  } finally {
    isLoading.value = false
  }
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
          <h2 class="w-96 text-white text-3xl font-bold font-inter">Sign up to TimeLock</h2>
        </div>
        <form @submit.prevent="handleSignup">
          <div class="flex flex-col gap-6">
            <div v-if="errorMsg" class="text-red-400 font-inter mb-2">{{ errorMsg }}</div>
            <div v-if="successMsg" class="text-green-400 font-inter mb-2">{{ successMsg }}</div>
            <div class="flex flex-col gap-3">
              <label class="font-semibold font-inter">Email</label>
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
              <label class="font-semibold font-inter">Password</label>
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
              <label class="font-semibold font-inter">Confirm Password</label>
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
              :disabled="isLoading"
              class="shadow-xl py-6 h-20 bg-black rounded-xl inline-flex justify-center items-center text-white text-lg font-bold font-inter hover:opacity-80 hover:scale-105 transition-all duration-300 ease-in-out"
            >
              {{ isLoading ? 'Creating Account...' : 'Create Account' }}
            </button>
            <button
              @click="props.closeSignup"
              type="button"
              class="shadow-xl py-6 h-12 bg-white rounded-xl inline-flex justify-center items-center text-black text-lg font-bold font-inter hover:opacity-80 hover:scale-105 transition-all duration-300 ease-in-out"
            >
              Cancel
            </button>
            <p
              class="bg-inherit text-white text-sm font-normal font-inter text-center justify-start"
            >
              Already have an account?
              <span
                class="text-indigo-400 text-sm font-normal font-inter underline hover:cursor-pointer hover:text-indigo-500"
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
