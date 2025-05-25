<script setup>
import { ref, computed } from 'vue'
import createMessage from '@/assets/img/createMessage.png'
import quill from '@/assets/img/quill.png'

defineOptions({
  name: 'CreateMessage',
})

const message = ref('')
const selectedDay = ref('')
const selectedMonth = ref('')
const selectedYear = ref('')
const showDateDropdown = ref(false)
const isLoading = ref(false)
const errorMsg = ref('')
const successMsg = ref('')
const isRecurringMode = ref(false)
const recurringInterval = ref(1)
const messages = ref([''])

// Generate arrays for days, months, and years
const days = Array.from({ length: 31 }, (_, i) => i + 1)
const months = [
  'January',
  'February',
  'March',
  'April',
  'May',
  'June',
  'July',
  'August',
  'September',
  'October',
  'November',
  'December',
]
const currentYear = new Date().getFullYear()
const years = Array.from({ length: 10 }, (_, i) => currentYear + i)

const formattedDate = computed(() => {
  if (!selectedDay.value || !selectedMonth.value || !selectedYear.value) return ''
  return `${months[selectedMonth.value]} ${selectedDay.value}, ${selectedYear.value}`
})

const toggleDateDropdown = () => {
  showDateDropdown.value = !showDateDropdown.value
  if (showDateDropdown.value && isRecurringMode.value) {
    // Wait for the dropdown to render
    setTimeout(() => {
      const container = document.querySelector('.overflow-y-auto')
      if (container) {
        container.scrollTo({
          top: container.scrollHeight,
          behavior: 'smooth',
        })
      }
    }, 50)
  }
}

const toggleRecurringMode = () => {
  isRecurringMode.value = !isRecurringMode.value
  if (!isRecurringMode.value) {
    messages.value = ['']
    recurringInterval.value = 1
  }
}

const addMessage = () => {
  messages.value.push('')
}

const removeMessage = (index) => {
  messages.value.splice(index, 1)
}

const handleSubmit = async () => {
  if (!selectedDay.value || !selectedMonth.value || !selectedYear.value) {
    errorMsg.value = 'Please fill in all fields'
    return
  }

  if (isRecurringMode.value) {
    // Check if all messages are filled
    if (messages.value.some((msg) => !msg.trim())) {
      errorMsg.value = 'Please fill in all messages'
      return
    }
  } else if (!message.value.trim()) {
    errorMsg.value = 'Please fill in the message'
    return
  }

  const selectedDate = new Date(selectedYear.value, selectedMonth.value, selectedDay.value)
  const today = new Date()

  if (selectedDate <= today) {
    errorMsg.value = 'Please select a future date'
    return
  }

  isLoading.value = true
  errorMsg.value = ''
  successMsg.value = ''

  try {
    const storedMessages = JSON.parse(localStorage.getItem('timelock_messages') || '[]')

    if (isRecurringMode.value) {
      const threadId = Date.now()
      messages.value.forEach((messageContent, index) => {
        const nextDate = new Date(selectedYear.value, selectedMonth.value, selectedDay.value)
        nextDate.setFullYear(nextDate.getFullYear() + index * recurringInterval.value)

        storedMessages.push({
          id: Date.now() + index,
          content: messageContent,
          threadId: threadId,
          sequenceNumber: index + 1,
          totalInSequence: messages.value.length,
          openDate: {
            month: nextDate.getMonth() + 1,
            day: nextDate.getDate(),
            year: nextDate.getFullYear(),
          },
          isOpened: false,
        })
      })
    } else {
      storedMessages.push({
        id: Date.now(),
        content: message.value,
        openDate: {
          month: parseInt(selectedMonth.value) + 1,
          day: parseInt(selectedDay.value),
          year: parseInt(selectedYear.value),
        },
        isOpened: false,
      })
    }

    localStorage.setItem('timelock_messages', JSON.stringify(storedMessages))

    // Clear form
    message.value = ''
    messages.value = ['']
    selectedDay.value = ''
    selectedMonth.value = ''
    selectedYear.value = ''
    recurringInterval.value = 1
    isRecurringMode.value = false
    successMsg.value = isRecurringMode.value
      ? 'Time capsule chapters created successfully!'
      : 'Message created successfully!'

    // Set timer to clear success message
    setTimeout(() => {
      successMsg.value = ''
    }, 3000) // 3 seconds
  } catch (error) {
    console.error('Failed to create message:', error)
    errorMsg.value = 'Failed to create message. Please try again.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div
    class="w-full h-[950px] flex justify-center items-center absolute inset-0 bg-center bg-no-repeat bg-contain mt-10"
    :style="{
      backgroundImage: `url(${createMessage})`,
      backgroundPosition: '43% center',
      zIndex: '-1',
    }"
  >
    <div class="relative w-[600px] p-8 z-1 mt-5">
      <h2 class="text-3xl font-bold font-spaceMono mb-3 text-center">
        {{ isRecurringMode ? 'CREATE TIME CAPSULE CHAPTERS' : 'CREATE TIMELOCK MESSAGE' }}
      </h2>

      <form @submit.prevent="handleSubmit" class="flex flex-col gap-6 relative">
        <!-- Error Message -->
        <div v-if="errorMsg" class="bg-red-500 bg-opacity-20 text-red-400 p-4 rounded-lg">
          {{ errorMsg }}
        </div>

        <!-- Single Message Mode -->
        <div v-if="!isRecurringMode" class="flex flex-col gap-6">
          <!-- Single Message Input -->
          <div class="flex flex-col gap-2">
            <label class="font-semibold font-spaceMono">YOUR MESSAGE</label>
            <textarea
              v-model="message"
              rows="6"
              class="w-full p-4 rounded-lg bg-white text-black resize-none focus:outline-none focus:ring-2 focus:ring-white"
              placeholder="Write your message here..."
              required
            ></textarea>
          </div>

          <!-- Date Selection for Single Message -->
          <div class="flex flex-col gap-2 relative">
            <label class="font-semibold font-spaceMono">UNLOCK DATE</label>
            <button
              type="button"
              @click="toggleDateDropdown"
              class="w-full px-4 py-2 rounded-lg bg-white text-black text-left focus:outline-none focus:ring-2 focus:ring-white"
            >
              {{ formattedDate || 'Select date' }}
            </button>

            <!-- Date Dropdown Panel -->
            <div
              v-if="showDateDropdown"
              class="absolute top-full left-0 w-full mt-2 bg-black border border-white rounded-lg p-6 z-50"
            >
              <div class="grid grid-cols-3 gap-4">
                <!-- Month Selection -->
                <div class="flex flex-col gap-2">
                  <label class="font-semibold font-spaceMono text-sm">MONTH</label>
                  <select
                    v-model="selectedMonth"
                    class="w-full px-3 py-2 rounded bg-white text-black focus:outline-none focus:ring-2 focus:ring-white"
                  >
                    <option value="" disabled>Month</option>
                    <option v-for="(month, index) in months" :key="month" :value="index">
                      {{ month }}
                    </option>
                  </select>
                </div>

                <!-- Day Selection -->
                <div class="flex flex-col gap-2">
                  <label class="font-semibold font-spaceMono text-sm">DAY</label>
                  <select
                    v-model="selectedDay"
                    class="w-full px-3 py-2 rounded bg-white text-black focus:outline-none focus:ring-2 focus:ring-white"
                  >
                    <option value="" disabled>Day</option>
                    <option v-for="day in days" :key="day" :value="day">
                      {{ day }}
                    </option>
                  </select>
                </div>

                <!-- Year Selection -->
                <div class="flex flex-col gap-2">
                  <label class="font-semibold font-spaceMono text-sm">YEAR</label>
                  <select
                    v-model="selectedYear"
                    class="w-full px-3 py-2 rounded bg-white text-black focus:outline-none focus:ring-2 focus:ring-white"
                  >
                    <option value="" disabled>Year</option>
                    <option v-for="year in years" :key="year" :value="year">
                      {{ year }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recurring Mode -->
        <div v-if="isRecurringMode" class="overflow-y-auto max-h-[600px] pr-4">
          <!-- Multiple Messages Input -->
          <div class="flex flex-col gap-6 h-80">
            <div v-for="(msg, index) in messages" :key="index" class="flex flex-col gap-2">
              <div class="flex justify-between items-center">
                <label class="font-semibold font-spaceMono">CHAPTER {{ index + 1 }}</label>
                <button
                  v-if="messages.length > 1"
                  @click="removeMessage(index)"
                  type="button"
                  class="text-red-400 hover:text-red-600 transition-colors"
                >
                  Remove
                </button>
              </div>
              <textarea
                v-model="messages[index]"
                rows="6"
                class="w-full p-4 rounded-lg bg-white text-black resize-none focus:outline-none focus:ring-2 focus:ring-white"
                :placeholder="`Write your message for chapter ${index + 1}...`"
                required
              ></textarea>
            </div>

            <button
              @click="addMessage"
              type="button"
              class="self-start px-4 py-2 bg-white text-black rounded-lg hover:opacity-80 transition-opacity font-spaceMono"
            >
              + Add Another Chapter
            </button>

            <!-- Recurring Options -->
            <div class="flex flex-col gap-4">
              <div class="flex flex-col gap-2">
                <label class="font-semibold font-spaceMono">YEARS BETWEEN CHAPTERS</label>
                <input
                  v-model="recurringInterval"
                  type="number"
                  min="1"
                  max="10"
                  class="w-full p-4 rounded-lg bg-white text-black focus:outline-none focus:ring-2 focus:ring-white"
                />
              </div>
            </div>

            <!-- Date Selection -->
            <div class="flex flex-col gap-2 relative">
              <label class="font-semibold font-spaceMono">FIRST CHAPTER UNLOCK DATE</label>
              <button
                type="button"
                @click="toggleDateDropdown"
                class="w-full px-4 py-2 rounded-lg bg-white text-black text-left focus:outline-none focus:ring-2 focus:ring-white"
              >
                {{ formattedDate || 'Select date' }}
              </button>

              <!-- Date Dropdown Panel -->
              <div
                v-if="showDateDropdown"
                class="absolute top-full left-0 w-full mt-2 bg-black border border-white rounded-lg p-6 z-50"
              >
                <div class="grid grid-cols-3 gap-4">
                  <!-- Month Selection -->
                  <div class="flex flex-col gap-2">
                    <label class="font-semibold font-spaceMono text-sm">MONTH</label>
                    <select
                      v-model="selectedMonth"
                      class="w-full px-3 py-2 rounded bg-white text-black focus:outline-none focus:ring-2 focus:ring-white"
                    >
                      <option value="" disabled>Month</option>
                      <option v-for="(month, index) in months" :key="month" :value="index">
                        {{ month }}
                      </option>
                    </select>
                  </div>

                  <!-- Day Selection -->
                  <div class="flex flex-col gap-2">
                    <label class="font-semibold font-spaceMono text-sm">DAY</label>
                    <select
                      v-model="selectedDay"
                      class="w-full px-3 py-2 rounded bg-white text-black focus:outline-none focus:ring-2 focus:ring-white"
                    >
                      <option value="" disabled>Day</option>
                      <option v-for="day in days" :key="day" :value="day">
                        {{ day }}
                      </option>
                    </select>
                  </div>

                  <!-- Year Selection -->
                  <div class="flex flex-col gap-2">
                    <label class="font-semibold font-spaceMono text-sm">YEAR</label>
                    <select
                      v-model="selectedYear"
                      class="w-full px-3 py-2 rounded bg-white text-black focus:outline-none focus:ring-2 focus:ring-white"
                    >
                      <option value="" disabled>Year</option>
                      <option v-for="year in years" :key="year" :value="year">
                        {{ year }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="isLoading"
          class="mt-4 py-4 bg-white rounded-lg text-black text-lg font-bold font-spaceMono animated disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{
            isLoading
              ? 'Creating...'
              : isRecurringMode
                ? 'Create Time Capsule Chapters'
                : 'Create Message'
          }}
        </button>

        <!-- Success Message -->
        <Transition name="fade">
          <div
            v-if="successMsg"
            class="absolute -bottom-20 left-0 right-0 bg-green-500 bg-opacity-20 text-green-400 p-4 rounded-lg text-center"
          >
            {{ successMsg }}
          </div>
        </Transition>
      </form>
    </div>
    <img
      :src="quill"
      alt="quill"
      class="fixed bottom-0 right-0 cursor-pointer transition-transform duration-300 hover:scale-110"
      :class="{ 'rotate-45': isRecurringMode }"
      @click="toggleRecurringMode"
    />
  </div>
</template>

<style scoped>
.animated {
  @apply hover:opacity-80 hover:scale-105 transition-all duration-300 ease-in-out;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

select {
  @apply appearance-none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='black'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.5rem center;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
}

/* Custom Scrollbar Styles */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.3);
  border-radius: 20px;
  border: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background-color: rgba(255, 255, 255, 0.5);
}
</style>
