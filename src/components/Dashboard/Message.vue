<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import jar from '@/assets/img/jar.png'
import star from '@/assets/img/star.png'
import starGlow from '@/assets/img/starGlow.png'
import starShine from '@/assets/img/starShine.png'
import envelope from '@/assets/img/envelope.png'

// Number of stars is now computed based on the messages array length
const numberOfStars = computed(() => user.value.messages.length)
const rotations = [15, -10, 25, -20, 35]
const statusOverlay = ref(true)
const envelopeStatus = ref(false)
const hoveredIndex = ref(null)
const openDate = ref('')
const showMessageOverlay = ref(false)
const selectedMessageIndex = ref(null)

// Add timer related refs and functions
const timer = ref(null)
const remainingTime = ref('00:00:00')

// Sample user data structure for future backend integration
const user = ref({
  messages: [
    {
      id: 1,
      content: "You're a shining star!",
      openDate: {
        month: 12,
        day: 25,
        year: 2023,
      },
      isOpened: false,
    },
    {
      id: 2,
      content: 'Reach for the stars!',
      openDate: {
        month: 1,
        day: 1,
        year: 2024,
      },
      isOpened: false,
    },
    {
      id: 3,
      content: 'Twinkle, twinkle!',
      openDate: {
        month: 2,
        day: 14,
        year: 2040,
      },
      isOpened: false,
    },
    {
      id: 4,
      content: 'Keep dreaming big!',
      openDate: {
        month: 3,
        day: 17,
        year: 2024,
      },
      isOpened: false,
    },
    {
      id: 5,
      content: 'Starlight, star bright!',
      openDate: {
        month: 4,
        day: 20,
        year: 2026,
      },
      isOpened: false,
    },
    {
      id: 6,
      content: 'Shine on!',
      openDate: {
        month: 5,
        day: 25,
        year: 2027,
      },
      isOpened: false,
    },
    {
      id: 7,
      content: 'Cosmic wonder!',
      openDate: {
        month: 6,
        day: 30,
        year: 2026,
      },
      isOpened: false,
    },
    {
      id: 8,
      content: 'Galactic glow!',
      openDate: {
        month: 7,
        day: 15,
        year: 2025,
      },
      isOpened: false,
    },
  ],
})

// Function to check if a message is ready to open (current date >= message date)
const isMessageReadyToOpen = (message) => {
  const currentDate = new Date()
  const messageDate = new Date(
    message.openDate.year,
    message.openDate.month - 1,
    message.openDate.day,
  )
  return currentDate >= messageDate
}

// Format the date for display
const formatDate = (dateObj) => {
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
  return `${months[dateObj.month - 1]} ${dateObj.day}, ${dateObj.year}`
}

const getStarSrc = (index) => {
  const messageIndex = index % user.value.messages.length
  const message = user.value.messages[messageIndex]

  // If the message is ready to open show shining star
  if (isMessageReadyToOpen(message)) {
    return starShine
  }

  // Otherwise use normal hover behavior
  return hoveredIndex.value === index ? starGlow : star
}

const getEnvelopeSrc = () => {
  // In future, could return different envelope image if opened
  return hoveredIndex.value === envelopeStatus.value ? starGlow : envelope
}

const handleMouseEnter = (index) => {
  hoveredIndex.value = index
  statusOverlay.value = true
  openDate.value = formatDate(user.value.messages[index].openDate)
  // Update the timer immediately when hovering
  updateTimer()
}

const handleMouseLeave = () => {
  hoveredIndex.value = null
  statusOverlay.value = false
}

const handleStarClick = (index) => {
  const messageIndex = index % user.value.messages.length
  const message = user.value.messages[messageIndex]

  // Only show overlay if message is ready to be opened
  if (isMessageReadyToOpen(message)) {
    selectedMessageIndex.value = messageIndex
    showMessageOverlay.value = true
    // Mark as opened
    message.isOpened = true
  }
}

const closeMessageOverlay = () => {
  showMessageOverlay.value = false
  selectedMessageIndex.value = null
}

const deleteMessage = (index) => {
  // In a real app, this would call an API to delete the message
  // For now, we'll just remove it from the array
  if (index !== null && index >= 0 && index < user.value.messages.length) {
    user.value.messages.splice(index, 1)
    closeMessageOverlay()
  }
}

const calculateTimeRemaining = (targetDate) => {
  const now = new Date()
  const target = new Date(targetDate.year, targetDate.month - 1, targetDate.day)
  target.setHours(0, 0, 0, 0) // Set to midnight

  const diff = target - now
  if (diff <= 0) return '00:00:00'

  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
  const seconds = Math.floor((diff % (1000 * 60)) / 1000)

  return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
}

const updateTimer = () => {
  if (hoveredIndex.value !== null) {
    const message = user.value.messages[hoveredIndex.value]
    remainingTime.value = calculateTimeRemaining(message.openDate)
  }
}

onMounted(() => {
  timer.value = setInterval(updateTimer, 1000)
})

onUnmounted(() => {
  if (timer.value) {
    clearInterval(timer.value)
  }
})
</script>

<template>
  <div class="flex items-center justify-evenly">
    <div
      class="flex scale-110 overflow-auto -mr-20 content-start flex-wrap-reverse justify-evenly w-[450px] h-[650px] p-14 bg-center bg-no-repeat bg-contain"
      :style="{ backgroundImage: `url(${jar})` }"
    >
      <img
        v-for="(n, index) in numberOfStars"
        :key="index"
        class="w-20 h-20 z-20 hover:cursor-pointer rounded-[100px]"
        :style="{ transform: `rotate(${rotations[index % rotations.length]}deg)` }"
        :src="getStarSrc(index)"
        @mouseenter="handleMouseEnter(index)"
        @mouseleave="handleMouseLeave"
        @click="handleStarClick(index)"
        alt="Star"
      />
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
              <p class="font-spaceMono bg-transparent text-2xl mb-[-15px]">
                {{
                  hoveredIndex !== null && isMessageReadyToOpen(user.messages[hoveredIndex])
                    ? 'OPENED ON'
                    : 'OPEN ON'
                }}
              </p>
              <p class="font-spaceMono bg-transparent text-2xl">{{ openDate }}</p>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>

  <!-- Message Overlay -->
  <transition name="fade">
    <div
      v-if="showMessageOverlay"
      class="fixed inset-0 h-screen w-screen flex items-center justify-center bg-black bg-opacity-70 z-50"
      @click.self="closeMessageOverlay"
    >
      <div
        class="bg-white rounded-lg relative overflow-hidden shadow-2xl max-h-[80vh] w-[90%] max-w-[500px]"
      >
        <button
          class="absolute top-2 right-4 text-3xl font-bold hover:text-gray-700 z-10"
          @click="closeMessageOverlay"
        >
          ×
        </button>
        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 p-6 text-white">
          <h2 class="text-2xl font-bold font-spaceMono">Your Message</h2>
          <span
            class="text-sm opacity-80 block mt-1 font-spaceMono"
            v-if="selectedMessageIndex !== null"
          >
            {{ formatDate(user.messages[selectedMessageIndex].openDate) }}
          </span>
        </div>
        <div class="p-6">
          <p
            class="text-xl font-spaceMono leading-relaxed text-black bg-transparent"
            v-if="selectedMessageIndex !== null"
          >
            {{ user.messages[selectedMessageIndex].content }}
          </p>
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-end">
          <button
            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded font-spaceMono transition-colors"
            @click="deleteMessage(selectedMessageIndex)"
          >
            Delete Message
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<style scoped>
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
</style>
