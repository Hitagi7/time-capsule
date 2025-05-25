<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import jar from '@/assets/img/jar.png'
import star from '@/assets/img/star.png'
import starGlow from '@/assets/img/starGlow.png'
import starShine from '@/assets/img/starShine.png'
import envelope from '@/assets/img/envelope.png'
import createMessage from '@/assets/img/createMessage.png'
import quill from '@/assets/img/quill.png'

const tab = ref('message')
const setTab = (val) => { tab.value = val }

const userEmail = localStorage.getItem('userEmail')
const user = ref({ messages: [] })
const selectedChapter = ref(null)

const fetchMessages = async () => {
  if (!userEmail) return
  const res = await fetch('http://localhost/Finals/time-capsule/src/backend/get_messages.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ userEmail }),
  })
  const data = await res.json()
  if (data.success) {
    user.value.messages = data.messages
    initializeMessages()
  }
}

const deleteMessage = async (index) => {
  const group = groupedMessages.value[index]
  if (group.type === 'sequence') {
    for (const msg of group.messages) {
      await fetch('http://localhost/Finals/time-capsule/src/backend/delete_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: msg.id, userEmail }),
      })
    }
  } else {
    const msg = group.messages[0]
    await fetch('http://localhost/Finals/time-capsule/src/backend/delete_message.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: msg.id, userEmail }),
    })
  }
  fetchMessages()
  closeMessageOverlay()
}

const rotations = [15, -10, 25, -20, 35]
const statusOverlay = ref(false)
const envelopeStatus = ref(false)
const hoveredIndex = ref(null)
const openDate = ref('')
const showMessageOverlay = ref(false)
const selectedMessageIndex = ref(null)
const timer = ref(null)
const remainingTime = ref('00:00:00')

const isPartOfSequence = (message) => {
  return message.threadId !== undefined && message.sequenceNumber !== undefined
}

const getSequenceMessages = (threadId) => {
  return user.value.messages.filter((msg) => msg.threadId === threadId)
}

const canOpenChapter = (message) => {
  if (!isPartOfSequence(message)) return true
  const sequence = getSequenceMessages(message.threadId)
  const previousChapters = sequence.filter((msg) => msg.sequenceNumber < message.sequenceNumber)
  return previousChapters.every((msg) => msg.isOpened)
}

const isMessageReadyToOpen = (message) => {
  const now = new Date()
  const currentDate = new Date(now.getFullYear(), now.getMonth(), now.getDate())
  const messageDate = new Date(
    message.openDate.year,
    message.openDate.month - 1,
    message.openDate.day,
  )
  const isReady = currentDate.getTime() >= messageDate.getTime()
  const canOpen = !isPartOfSequence(message) || canOpenChapter(message)
  return isReady && canOpen
}

const formatDate = (dateObj) => {
  if (!dateObj) return ''
  const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
  ]
  return `${months[dateObj.month - 1]} ${dateObj.day}, ${dateObj.year}`
}

const groupedMessages = computed(() => {
  const groups = {}
  const standalone = []

  user.value.messages.forEach((message) => {
    if (isPartOfSequence(message)) {
      if (!groups[message.threadId]) {
        groups[message.threadId] = []
      }
      groups[message.threadId].push(message)
    } else {
      standalone.push(message)
    }
  })

  const sequences = Object.values(groups).map((sequence) => {
    sequence.sort((a, b) => a.sequenceNumber - b.sequenceNumber)
    return {
      type: 'sequence',
      threadId: sequence[0].threadId,
      messages: sequence,
      openDate: sequence[0].openDate,
      firstUnopened: sequence.find((msg) => !msg.isOpened),
      totalChapters: sequence.length,
      openedChapters: sequence.filter((msg) => msg.isOpened).length,
    }
  })

  const standaloneFormatted = standalone.map((message) => ({
    type: 'standalone',
    messages: [message],
    openDate: message.openDate,
  }))

  return [...sequences, ...standaloneFormatted]
})

const getStarSrc = (index) => {
  const group = groupedMessages.value[index]
  if (group.type === 'sequence') {
    const allChaptersOpened = group.messages.every((msg) => msg.isOpened)
    return allChaptersOpened ? starShine : star
  } else {
    const message = group.messages[0]
    const messageDate = new Date(
      message.openDate.year,
      message.openDate.month - 1,
      message.openDate.day,
    )
    messageDate.setHours(0, 0, 0, 0)
    const now = new Date()
    const currentDate = new Date(now.getFullYear(), now.getMonth(), now.getDate())
    currentDate.setHours(0, 0, 0, 0)
    return currentDate >= messageDate && message.isOpened ? starShine : star
  }
}

const getEnvelopeSrc = () => {
  return hoveredIndex.value === envelopeStatus.value ? starGlow : envelope
}

const handleMouseEnter = (index) => {
  hoveredIndex.value = index
  statusOverlay.value = true
  const group = groupedMessages.value[index]
  if (group.type === 'sequence') {
    const nextChapter = group.messages.find((msg) => !msg.isOpened)
    if (nextChapter) {
      openDate.value = formatDate(nextChapter.openDate)
    } else {
      openDate.value = formatDate(group.messages[group.messages.length - 1].openDate)
    }
  } else {
    openDate.value = formatDate(group.messages[0].openDate)
  }
  updateTimer()
}

const handleMouseLeave = () => {
  hoveredIndex.value = null
  statusOverlay.value = false
}

const handleStarClick = (index) => {
  const group = groupedMessages.value[index]
  if (group.type === 'sequence') {
    group.messages.forEach((msg) => {
      if (isMessageReadyToOpen(msg)) {
        msg.isOpened = true
      }
    })
    selectedMessageIndex.value = index
    showMessageOverlay.value = true
  } else {
    const message = group.messages[0]
    if (isMessageReadyToOpen(message)) {
      message.isOpened = true
      selectedMessageIndex.value = index
      showMessageOverlay.value = true
    }
  }
}

const closeMessageOverlay = () => {
  showMessageOverlay.value = false
  selectedMessageIndex.value = null
  selectedChapter.value = null
}

const calculateTimeRemaining = (targetDate) => {
  const now = new Date()
  const target = new Date(targetDate.year, targetDate.month - 1, targetDate.day)
  target.setHours(0, 0, 0, 0)
  const diff = target - now
  if (diff <= 0) return '00:00:00'
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
  const seconds = Math.floor((diff % (1000 * 60)) / 1000)
  return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
}

const updateTimer = () => {
  if (hoveredIndex.value !== null) {
    const group = groupedMessages.value[hoveredIndex.value]
    if (group.type === 'sequence') {
      const nextChapter = group.messages.find((msg) => !msg.isOpened)
      if (nextChapter) {
        remainingTime.value = calculateTimeRemaining(nextChapter.openDate)
      } else {
        remainingTime.value = '00:00:00'
      }
    } else {
      remainingTime.value = calculateTimeRemaining(group.messages[0].openDate)
    }
  }
}

const handleChapterClick = (msg) => {
  if (msg.isOpened) {
    selectedChapter.value = msg
  }
}

const initializeMessages = () => {
  const now = new Date()
  const currentDate = new Date(now.getFullYear(), now.getMonth(), now.getDate())
  currentDate.setHours(0, 0, 0, 0)
  user.value.messages.forEach((msg) => {
    const msgDate = new Date(msg.openDate.year, msg.openDate.month - 1, msg.openDate.day)
    msgDate.setHours(0, 0, 0, 0)
    if (currentDate >= msgDate) {
      msg.isOpened = true
    }
  })
}

onMounted(() => {
  fetchMessages()
  timer.value = setInterval(() => {
    updateTimer()
    initializeMessages()
  }, 1000)
})

onUnmounted(() => {
  if (timer.value) {
    clearInterval(timer.value)
  }
})

// --- CREATE TAB (backend, recurring/sequence support) ---
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

const days = Array.from({ length: 31 }, (_, i) => i + 1)
const months = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
]
const currentYear = new Date().getFullYear()
const years = Array.from({ length: 10 }, (_, i) => currentYear + i)

const formattedDate = computed(() => {
  if (!selectedDay.value || selectedMonth.value === '' || !selectedYear.value) return ''
  return `${months[selectedMonth.value]} ${selectedDay.value}, ${selectedYear.value}`
})

const toggleDateDropdown = () => {
  showDateDropdown.value = !showDateDropdown.value
  if (showDateDropdown.value && isRecurringMode.value) {
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

const addMessageInput = () => {
  messages.value.push('')
}

const removeMessageInput = (index) => {
  messages.value.splice(index, 1)
}

const handleSubmit = async () => {
  if (!selectedDay.value || selectedMonth.value === '' || !selectedYear.value) {
    errorMsg.value = 'Please fill in all fields'
    return
  }

  if (isRecurringMode.value) {
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
  today.setHours(0, 0, 0, 0)
  if (selectedDate <= today) {
    errorMsg.value = 'Please select a future date'
    return
  }

  isLoading.value = true
  errorMsg.value = ''
  successMsg.value = ''

  try {
    if (isRecurringMode.value) {
      // Use add_sequence.php for recurring messages
      await fetch('http://localhost/Finals/time-capsule/src/backend/add_sequence.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          userEmail,
          messages: messages.value,
          startDate: {
            year: parseInt(selectedYear.value),
            month: parseInt(selectedMonth.value) + 1,
            day: parseInt(selectedDay.value),
          },
          recurringInterval: parseInt(recurringInterval.value),
        }),
      })
    } else {
      await fetch('http://localhost/Finals/time-capsule/src/backend/add_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          userEmail,
          content: message.value,
          openDate: {
            month: parseInt(selectedMonth.value) + 1,
            day: parseInt(selectedDay.value),
            year: parseInt(selectedYear.value),
          },
        }),
      })
    }

    await fetchMessages()

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

    setTimeout(() => {
      successMsg.value = ''
    }, 3000)
  } catch (error) {
    console.error('Failed to create message:', error)
    errorMsg.value = 'Failed to create message. Please try again.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div>
    <!-- Tabs -->
    <div
      class="flex justify-center py-12 text-2xl font-bold font-spaceMono leading-10 tracking-widest gap-1"
    >
      <button
        @click="setTab('message')"
        class="w-36 h-14 border border-white animated"
        :class="tab === 'message' ? 'text-black border-black bg-white' : 'text-white border-white bg-black'"
      >
        MESSAGE
      </button>
      <button
        @click="setTab('create')"
        class="w-36 h-14 bg-black border border-white animated"
        :class="tab === 'create' ? 'text-black border-black bg-white' : 'text-white border-white bg-black'"
      >
        CREATE
      </button>
    </div>

    <!-- CREATE TAB -->
    <div
      v-if="tab === 'create'"
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
                    @click="removeMessageInput(index)"
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
                @click="addMessageInput"
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
      <!-- Floating Quill Icon for toggling recurring mode -->
      <img
        :src="quill"
        alt="quill"
        class="fixed bottom-20 right-80 w-42 h-42 z-60 cursor-pointer transition-transform duration-300 hover:rotate-45 hover:scale-190"
        @click="toggleRecurringMode"
    />
    </div>

    <!-- MESSAGE TAB (jar, stars, overlays, etc) -->
    <div v-if="tab === 'message'" class="flex items-center justify-evenly">
      <div
        class="flex scale-110 overflow-auto -mr-20 content-start flex-wrap-reverse justify-evenly w-[450px] h-[650px] p-14 bg-center bg-no-repeat bg-contain"
        :style="{ backgroundImage: `url(${jar})` }"
      >
        <div
          v-for="(group, index) in groupedMessages"
          :key="group.type === 'sequence' ? `seq-${group.threadId}` : `msg-${group.messages[0].id}`"
          class="relative inline-block"
        >
          <img
            class="w-20 h-20 z-20 hover:cursor-pointer rounded-[100px]"
            :style="{ transform: `rotate(${rotations[index % rotations.length]}deg)` }"
            :src="getStarSrc(index)"
            @mouseenter="handleMouseEnter(index)"
            @mouseleave="handleMouseLeave"
            @click="handleStarClick(index)"
            alt="Star"
          />
          <button
            @click.stop="deleteMessage(index)"
            class="absolute -top-2 -right-2 bg-gray-200 text-black rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold shadow hover:bg-gray-400 transition"
            title="Delete"
          >
            ×
          </button>
        </div>
      </div>
      <div class="w-[700px] h-[650px] -ml-20 pl-20">
        <transition name="envelope">
          <div v-if="statusOverlay" class="animate-popUp origin-center">
            <div class="relative">
              <img :src="getEnvelopeSrc()" alt="Envelope" class="scale-110" />
              <p
                class="font-spaceMono bg-transparent absolute top-[40%] left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center text-4xl"
              >
                {{ remainingTime }} <br />
                left
              </p>
              <div
                class="absolute top-[69%] left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center"
              >
                <p class="font-spaceMono bg-transparent text-2xl -mb-[10px] mt-10">
                  {{
                    hoveredIndex !== null &&
                    ((groupedMessages[hoveredIndex].type === 'sequence' &&
                      groupedMessages[hoveredIndex].openedChapters ===
                        groupedMessages[hoveredIndex].totalChapters) ||
                      (groupedMessages[hoveredIndex].type === 'standalone' &&
                        isMessageReadyToOpen(groupedMessages[hoveredIndex].messages[0])))
                      ? 'OPENED ON'
                      : 'OPEN ON'
                  }}
                </p>
                <p class="font-spaceMono bg-transparent text-2xl">{{ openDate }}</p>
                <!-- Sequence Info -->
                <p
                  v-if="hoveredIndex !== null && groupedMessages[hoveredIndex].type === 'sequence'"
                  class="font-spaceMono text-xl text-gray-200 bg-gray-800/80 rounded-lg p-2"
                >
                  {{ groupedMessages[hoveredIndex].openedChapters }} of
                  {{ groupedMessages[hoveredIndex].totalChapters }} chapters opened
                  <span
                    v-if="
                      groupedMessages[hoveredIndex].firstUnopened &&
                      !canOpenChapter(groupedMessages[hoveredIndex].firstUnopened)
                    "
                    class="block text-sm text-red-400 mt-1"
                  >
                    Previous chapters must be opened first
                  </span>
                </p>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </div>

    <!-- Message Overlay -->
    <transition name="fade">
      <div
        v-if="showMessageOverlay && selectedMessageIndex !== null"
        class="fixed inset-0 h-screen w-screen flex items-center justify-center bg-black bg-opacity-70 z-50"
        @click.self="closeMessageOverlay"
      >
        <div
          class="flex gap-4"
          :class="{
            'w-[90%] max-w-[1200px]': groupedMessages[selectedMessageIndex].type === 'sequence',
            'w-[45%] max-w-[600px]': groupedMessages[selectedMessageIndex].type !== 'sequence',
          }"
        >
          <!-- Single Message or Chapters List View -->
          <div
            class="bg-white rounded-lg relative overflow-hidden shadow-2xl"
            :class="{
              'flex-1': groupedMessages[selectedMessageIndex].type === 'sequence',
              'w-full': groupedMessages[selectedMessageIndex].type !== 'sequence',
            }"
          >
            <button
              class="absolute top-2 right-4 text-3xl font-bold hover:text-gray-700 z-10"
              @click="closeMessageOverlay"
            >
              ×
            </button>
            <div class= "bg-black p-6 text-white">
              <h2 class="text-2xl font-bold font-spaceMono">
                {{
                  groupedMessages[selectedMessageIndex].type === 'sequence'
                    ? 'Time Capsule Chapters'
                    : 'Your Message'
                }}
              </h2>
              <span class="text-sm opacity-80 block mt-1 font-spaceMono">
                {{
                  groupedMessages[selectedMessageIndex].type === 'sequence'
                    ? `${groupedMessages[selectedMessageIndex].openedChapters} of ${groupedMessages[selectedMessageIndex].totalChapters} chapters opened`
                    : formatDate(groupedMessages[selectedMessageIndex].messages[0].openDate)
                }}
              </span>
            </div>

            <div class="p-6">
              <!-- Progress Bar for Sequences -->
              <div v-if="groupedMessages[selectedMessageIndex].type === 'sequence'" class="mb-6">
                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                  <div
                    class="h-full bg-gray-950 transition-all duration-500"
                    :style="{
                      width: `${
                        (groupedMessages[selectedMessageIndex].openedChapters /
                          groupedMessages[selectedMessageIndex].totalChapters) *
                        100
                      }%`,
                    }"
                  ></div>
                </div>
              </div>

              <!-- Chapter List -->
              <div
                v-if="groupedMessages[selectedMessageIndex].type === 'sequence'"
                class="space-y-4 max-h-[50vh] overflow-y-auto pr-2 custom-scrollbar"
              >
                <div
                  v-for="msg in groupedMessages[selectedMessageIndex].messages"
                  :key="msg.id"
                  class="p-4 rounded-lg border transition-all duration-200"
                  :class="[
                    msg.isOpened ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200',
                    msg === selectedChapter ? 'border-2 border-gray-950' : '',
                    msg.isOpened ? 'cursor-pointer hover:bg-green-100' : '',
                  ]"
                  @click="msg.isOpened && handleChapterClick(msg)"
                >
                  <div class="flex items-center justify-between">
                    <h3
                      class="font-spaceMono font-semibold"
                      :class="msg.isOpened ? 'text-green-700' : 'text-gray-700'"
                    >
                      Chapter {{ msg.sequenceNumber }}
                    </h3>
                    <span
                      class="px-3 py-1 rounded text-sm font-spaceMono"
                      :class="
                        msg.isOpened ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'
                      "
                    >
                      {{ msg.isOpened ? 'Opened' : 'Locked' }}
                    </span>
                  </div>
                  <p
                    class="font-spaceMono mt-2 px-6 py-2"
                    :class="
                      msg.isOpened
                        ? 'text-black bg-green-200 rounded-lg'
                        : 'text-gray-400 bg-gray-200 rounded-lg'
                    "
                  >
                    {{ formatDate(msg.openDate) }}
                  </p>
                </div>
              </div>

              <!-- Single Message Content -->
              <p
                v-if="groupedMessages[selectedMessageIndex].type === 'standalone'"
                class="text-xl font-spaceMono leading-relaxed text-black bg-transparent"
              >
                {{ groupedMessages[selectedMessageIndex].messages[0].content }}
              </p>
            </div>
            <div class="p-6 border-t border-gray-200 flex justify-end">
              <button
                class="bg-gray-950 hover:bg-black text-white px-4 py-2 rounded font-spaceMono transition-colors"
                @click="deleteMessage(selectedMessageIndex)"
              >
                Delete
                {{
                  groupedMessages[selectedMessageIndex].type === 'sequence'
                    ? 'Time Capsule'
                    : 'Message'
                }}
              </button>
            </div>
          </div>

          <!-- Chapter Content View (only shown for sequences) -->
          <div
            v-if="groupedMessages[selectedMessageIndex].type === 'sequence'"
            class="bg-white rounded-lg relative overflow-hidden shadow-2xl max-h-[80vh] flex-1"
          >
            <div class="bg-black p-6 text-white">
              <h2 class="text-2xl font-bold font-spaceMono">
                {{
                  selectedChapter ? `Chapter ${selectedChapter.sequenceNumber}` : 'Chapter Content'
                }}
              </h2>
              <span class="text-sm opacity-80 block mt-1 font-spaceMono">
                {{
                  selectedChapter
                    ? `Opened on ${formatDate(selectedChapter.openDate)}`
                    : 'Select a chapter to view'
                }}
              </span>
            </div>
            <div
              class="p-6 max-h-[calc(80vh-140px)] overflow-y-auto custom-scrollbar"
              :class="!selectedChapter ? 'flex items-center justify-center min-h-[300px]' : ''"
            >
              <template v-if="selectedChapter">
                <p
                  class="text-xl font-spaceMono leading-relaxed text-gray-950 bg-inherit whitespace-pre-wrap"
                >
                  {{ selectedChapter.content }}
                </p>
              </template>
              <template v-else>
                <div class="text-center">
                  <p class="text-xl font-spaceMono text-gray-500 bg-inherit">
                    Select a chapter to display its contents
                  </p>
                  <p class="mt-2 text-sm font-spaceMono text-gray-400 bg-inherit">
                    Click on any opened chapter from the list on the left
                  </p>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
.animated {
  @apply hover:opacity-80 hover:scale-105 transition-all duration-300 ease-in-out;
}

@keyframes popUp {
  0% {
    opacity: 0;
    transform: scale(0.5);
  }
  70% {
    transform: scale(1.1);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes popOut {
  0% {
    opacity: 1;
    transform: scale(1);
  }
  30% {
    transform: scale(1.1);
  }
  100% {
    opacity: 0;
    transform: scale(0.5);
  }
}

.animate-popUp {
  animation: popUp 0.3s ease-out;
}

.envelope-enter-active {
  animation: popUp 0.3s ease-out;
}

.envelope-leave-active {
  animation: popOut 0.3s ease-in;
}

/* Transition classes for the message overlay */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
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

.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgba(107, 114, 128, 0.3) transparent;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(107, 114, 128, 0.3);
  border-radius: 20px;
  border: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(107, 114, 128, 0.5);
}

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
