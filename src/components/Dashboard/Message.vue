<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import jar from '@/assets/img/jar.png'
import star from '@/assets/img/star.png'
import starGlow from '@/assets/img/starGlow.png'
import starShine from '@/assets/img/starShine.png'
import envelope from '@/assets/img/envelope.png'

// Number of stars is now computed based on the messages array length
// const numberOfStars = computed(() => groupedMessages.value.length)
const rotations = [15, -10, 25, -20, 35]
const statusOverlay = ref(false)
const envelopeStatus = ref(false)
const hoveredIndex = ref(null)
const openDate = ref('')
const showMessageOverlay = ref(false)
const selectedMessageIndex = ref(null)

// Add timer related refs and functions
const timer = ref(null)
const remainingTime = ref('00:00:00')

// Add new ref for selected chapter
const selectedChapter = ref(null)

// Sample user data structure for future backend integration
const user = ref({
  messages: [
    // First sequence (Past dates)
    {
      id: 1,
      content:
        'Chapter 1: The Beginning\n\nThis is where our journey begins. The first step into a new adventure.',
      threadId: 1,
      sequenceNumber: 1,
      totalInSequence: 3,
      openDate: {
        month: 12,
        day: 25,
        year: 2023,
      },
      isOpened: true, // This is opened
    },
    {
      id: 2,
      content:
        'Chapter 2: The Journey\n\nAs we continue our path, new challenges and opportunities arise.',
      threadId: 1,
      sequenceNumber: 2,
      totalInSequence: 3,
      openDate: {
        month: 1,
        day: 1,
        year: 2024,
      },
      isOpened: true, // This should also be opened since it's in the past
    },
    {
      id: 3,
      content:
        'Chapter 3: The Future\n\nWhat lies ahead is full of possibilities and dreams to be realized.',
      threadId: 1,
      sequenceNumber: 3,
      totalInSequence: 3,
      openDate: {
        month: 2,
        day: 14,
        year: 2025,
      },
      isOpened: false,
    },
    // Second sequence (Future dates)
    {
      id: 4,
      content: 'Year 1: New Beginnings\n\nStarting fresh with new goals and aspirations.',
      threadId: 2,
      sequenceNumber: 1,
      totalInSequence: 4,
      openDate: {
        month: 1,
        day: 1,
        year: 2025,
      },
      isOpened: false,
    },
    {
      id: 5,
      content:
        'Year 2: Growth\n\nLooking back at the progress made and forward to what comes next.',
      threadId: 2,
      sequenceNumber: 2,
      totalInSequence: 4,
      openDate: {
        month: 1,
        day: 1,
        year: 2026,
      },
      isOpened: false,
    },
    {
      id: 6,
      content: 'Year 3: Transformation\n\nEmbracing change and evolving into something new.',
      threadId: 2,
      sequenceNumber: 3,
      totalInSequence: 4,
      openDate: {
        month: 1,
        day: 1,
        year: 2027,
      },
      isOpened: false,
    },
    {
      id: 7,
      content: 'Year 4: Achievement\n\nCelebrating milestones and accomplishments.',
      threadId: 2,
      sequenceNumber: 4,
      totalInSequence: 4,
      openDate: {
        month: 1,
        day: 1,
        year: 2028,
      },
      isOpened: false,
    },
    // Standalone messages
    {
      id: 8,
      content: 'Happy Birthday! 🎉\n\nWishing you joy and success in the year ahead!',
      openDate: {
        month: 3,
        day: 17,
        year: 2024,
      },
      isOpened: false,
    },
    {
      id: 9,
      content:
        'Summer Memories 🌞\n\nRemembering the wonderful times we shared during summer break.',
      openDate: {
        month: 6,
        day: 21,
        year: 2024,
      },
      isOpened: false,
    },
    {
      id: 10,
      content: 'New Year Reflections 🎊\n\nThoughts and wishes for an amazing new year!',
      openDate: {
        month: 12,
        day: 31,
        year: 2024,
      },
      isOpened: false,
    },
  ],
})

// Function to check if a message is part of a sequence
const isPartOfSequence = (message) => {
  return message.threadId !== undefined && message.sequenceNumber !== undefined
}

// Function to get all messages in a sequence
const getSequenceMessages = (threadId) => {
  return user.value.messages.filter((msg) => msg.threadId === threadId)
}

// Function to check if previous chapters are opened
const canOpenChapter = (message) => {
  if (!isPartOfSequence(message)) return true
  const sequence = getSequenceMessages(message.threadId)
  const previousChapters = sequence.filter((msg) => msg.sequenceNumber < message.sequenceNumber)
  return previousChapters.every((msg) => msg.isOpened)
}

// Function to check if a message is ready to open (current date >= message date)
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

// Group messages by threadId or keep as individual messages
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

  // Sort sequences by first chapter's date
  const sequences = Object.values(groups).map((sequence) => {
    sequence.sort((a, b) => a.sequenceNumber - b.sequenceNumber)
    return {
      type: 'sequence',
      threadId: sequence[0].threadId,
      messages: sequence,
      openDate: sequence[0].openDate, // Use first chapter's date for the star
      firstUnopened: sequence.find((msg) => !msg.isOpened),
      totalChapters: sequence.length,
      openedChapters: sequence.filter((msg) => msg.isOpened).length,
    }
  })

  // Convert standalone messages to same format
  const standaloneFormatted = standalone.map((message) => ({
    type: 'standalone',
    messages: [message],
    openDate: message.openDate,
  }))

  return [...sequences, ...standaloneFormatted]
})

// Update getStarSrc to work with grouped messages
const getStarSrc = (index) => {
  const group = groupedMessages.value[index]

  if (group.type === 'sequence') {
    // For sequences, ONLY glow if ALL chapters in the sequence are opened
    // No partial glowing - it's all or nothing
    const allChaptersOpened = group.messages.every((msg) => msg.isOpened)
    return allChaptersOpened ? starShine : star
  } else {
    // For standalone messages - automatically glow if it's opened and date has passed
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

    // Automatically glow if the message is opened and date has passed
    return currentDate >= messageDate && message.isOpened ? starShine : star
  }
}

const getEnvelopeSrc = () => {
  // In future, could return different envelope image if opened
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
    // Get all unopened messages in the sequence
    const unopenedMessages = group.messages.filter((msg) => !msg.isOpened)

    // Check each unopened message
    unopenedMessages.forEach((msg) => {
      if (isMessageReadyToOpen(msg)) {
        msg.isOpened = true
      }
    })

    // Always show overlay for sequences, even if all messages are already opened
    selectedMessageIndex.value = index
    showMessageOverlay.value = true
  } else {
    // For standalone messages, check if ready to open
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
}

const deleteMessage = (index) => {
  if (index !== null && index >= 0) {
    const group = groupedMessages.value[index]
    if (group.type === 'sequence') {
      // Remove all messages in the sequence
      user.value.messages = user.value.messages.filter(
        (msg) => !isPartOfSequence(msg) || msg.threadId !== group.messages[0].threadId,
      )
    } else {
      // Remove single message
      const messageId = group.messages[0].id
      user.value.messages = user.value.messages.filter((msg) => msg.id !== messageId)
    }
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

// Add back and improve the initializeMessages function
const initializeMessages = () => {
  const now = new Date()
  const currentDate = new Date(now.getFullYear(), now.getMonth(), now.getDate())
  currentDate.setHours(0, 0, 0, 0)

  // Automatically open any messages whose date has passed
  user.value.messages.forEach((msg) => {
    const msgDate = new Date(msg.openDate.year, msg.openDate.month - 1, msg.openDate.day)
    msgDate.setHours(0, 0, 0, 0)

    // If message date has passed, automatically mark it as opened
    if (currentDate >= msgDate) {
      msg.isOpened = true
    }
  })
}

// Call initialization on component mount and set up periodic check
onMounted(() => {
  // Initial check
  initializeMessages()

  // Set up timers
  timer.value = setInterval(() => {
    updateTimer()
    initializeMessages() // Also check for new messages to open
  }, 1000)
})

onUnmounted(() => {
  if (timer.value) {
    clearInterval(timer.value)
  }
})

defineOptions({
  name: 'MessageDisplay',
})
</script>

<template>
  <div class="flex items-center justify-evenly">
    <div
      class="flex scale-110 overflow-auto -mr-20 content-start flex-wrap-reverse justify-evenly w-[450px] h-[650px] p-14 bg-center bg-no-repeat bg-contain"
      :style="{ backgroundImage: `url(${jar})` }"
    >
      <img
        v-for="(group, index) in groupedMessages"
        :key="group.type === 'sequence' ? `seq-${group.threadId}` : `msg-${group.messages[0].id}`"
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
          <div class="bg-gradient-to-r from-purple-500 to-indigo-600 p-6 text-white">
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
                  class="h-full bg-purple-500 transition-all duration-500"
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
                  msg === selectedChapter ? 'border-2 border-purple-400' : '',
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
              class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded font-spaceMono transition-colors"
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
          <div class="bg-gradient-to-r from-purple-500 to-indigo-600 p-6 text-white">
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
                class="text-xl font-spaceMono leading-relaxed text-black bg-inherit whitespace-pre-wrap"
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
</style>
