<script setup>
import { ref } from 'vue'
import jar from '@/assets/img/jar.png'
import star from '@/assets/img/star.png'
import starGlow from '@/assets/img/starGlow.png'

const numberOfStars = ref(8)
const rotations = [15, -10, 25, -20, 35]
const statusOverlay = ref(false)
const hoveredIndex = ref(null)
const userMessage = ref('')

const getStarSrc = (index) => {
  return hoveredIndex.value === index ? starGlow : star
}

const handleMouseEnter = (index) => {
  hoveredIndex.value = index
  statusOverlay.value = true
  userMessage.value = messages[index]
}

const handleMouseLeave = () => {
  hoveredIndex.value = null
  statusOverlay.value = false
}

// Sample
const messages = [
  "You're a shining star!",
  'Reach for the stars!',
  'Twinkle, twinkle!',
  'Keep dreaming big!',
  'Starlight, star bright!',
  'Shine on!',
  'Cosmic wonder!',
  'Galactic glow!',
]
</script>

<template>
  <div class="flex items-center justify-around">
    <div
      class="flex content-start flex-wrap-reverse justify-evenly w-[480px] h-[700px] p-14 bg-center bg-no-repeat bg-contain"
      :style="{ backgroundImage: `url(${jar})` }"
    >
      <img
        v-for="(n, index) in numberOfStars"
        :key="index"
        class="star-size z-20 hover:cursor-pointer rounded-[100px]"
        :style="{ transform: `rotate(${rotations[index % rotations.length]}deg)` }"
        :src="getStarSrc(index)"
        @mouseenter="handleMouseEnter(index)"
        @mouseleave="handleMouseLeave"
        alt="Star"
      />
    </div>
    <div class="w-96">
      <div v-if="statusOverlay">{{ userMessage }}</div>
    </div>
  </div>
</template>

<style scoped>
.star-size {
  @apply w-20 h-20;
}
</style>
