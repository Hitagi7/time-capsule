<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import mailIcon from '@/assets/icons/mailIcon.svg'
import passwordIcon from '@/assets/icons/passwordIcon.svg'

const router = useRouter()
const props = defineProps(['overlayOptions', 'closeLogin', 'openSignup'])

const showOverlay = computed(() => props.overlayOptions.show)

const email = ref('')
const password = ref('')
const errorMsg = ref('')
const isLoading = ref(false)

const handleLogin = async () => {
  errorMsg.value = ''
  isLoading.value = true

  try {
    const res = await fetch('http://localhost/time-capsule/src/backend/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value }),
    })
    const data = await res.json()
    if (data.success) {
      // Store user data
      localStorage.setItem('userEmail', email.value)
      // Close login overlay
      props.closeLogin()
      // Redirect to dashboard
      router.push('/dashboard')
    } else {
      errorMsg.value = data.message
    }
  } catch {
    errorMsg.value = 'Please Sign-Up if you dont have an account yet.'
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
        <div class="mb-8">
          <h2 class="w-96 text-white text-3xl font-bold font-inter">Log in to TimeLock</h2>
        </div>
        <form @submit.prevent="handleLogin">
          <div class="flex flex-col gap-6">
            <div v-if="errorMsg" class="text-red-400 font-inter mb-2">{{ errorMsg }}</div>
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
          </div>
          <div class="flex flex-col gap-6 mt-8">
            <button
              type="submit"
              :disabled="isLoading"
              class="shadow-xl py-6 h-20 bg-black rounded-xl inline-flex justify-center items-center text-white text-lg font-bold font-inter hover:opacity-80 hover:scale-105 transition-all duration-300 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ isLoading ? 'Logging in...' : 'Log in' }}
            </button>
            <button
              @click="props.closeLogin"
              type="button"
              :disabled="isLoading"
              class="shadow-xl py-6 h-12 bg-white rounded-xl inline-flex justify-center items-center text-black text-lg font-bold font-inter hover:opacity-80 hover:scale-105 transition-all duration-300 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Cancel
            </button>

            <p
              class="bg-inherit text-white text-sm font-normal font-inter text-center justify-start"
            >
              Need an account?
              <span
                class="text-indigo-400 text-sm font-normal font-inter underline hover:cursor-pointer hover:text-indigo-500"
                @click="props.openSignup"
                >Create an account.</span
              >
            </p>
          </div>
        </form>
      </div>
    </div>
  </transition>
</template>

<style>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition:
    opacity 0.3s ease,
    transform 0.3s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}

.fade-slide-enter-to {
  opacity: 1;
  transform: translateY(0);
}

.fade-slide-leave-from {
  opacity: 1;
  transform: translateY(0);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
