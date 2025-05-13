<script setup>
import { ref, computed } from 'vue'

const props = defineProps(['overlayOptions'])

// variable to control the visibility of the overlay using the passed object
const showOverlay = computed(() => props.overlayOptions.show)

const email = ref('')
const password = ref('')
const confirmPassword = ref('')

const closeSignupOverlay = () => {
  props.overlayOptions.show = false
}

const handleSignup = () => {
  if (password.value !== confirmPassword.value) {
    alert('Passwords do not match')
    return
  }

  console.log('Sign Up with', { email: email.value, password: password.value })
  closeSignupOverlay()
}
</script>

<template>
  <div
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    v-if="showOverlay"
  >
    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
      <div class="flex justify-between mb-4">
        <h2 class="text-xl font-semibold">Sign Up</h2>
        <button @click="closeSignupOverlay" class="text-gray-500">X</button>
      </div>
      <form @submit.prevent="handleSignup">
        <div class="mb-4">
          <label class="block text-gray-700">Email</label>
          <input type="email" v-model="email" class="w-full px-3 py-2 border rounded-lg" required />
        </div>
        <div class="mb-4">
          <label class="block text-gray-700">Password</label>
          <input
            type="password"
            v-model="password"
            class="w-full px-3 py-2 border rounded-lg"
            required
          />
        </div>
        <div class="mb-4">
          <label class="block text-gray-700">Confirm Password</label>
          <input
            type="password"
            v-model="confirmPassword"
            class="w-full px-3 py-2 border rounded-lg"
            required
          />
        </div>
        <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-lg w-full">
          Sign Up
        </button>
      </form>
    </div>
  </div>
</template>
