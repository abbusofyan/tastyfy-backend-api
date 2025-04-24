<script setup>
const getImageUrl = (imageName) => `/site_images/${imageName}`;
</script>

<template>
  <div class="h-[100vh] pb-10">
    <VRow
      class="min-h-screen bg-cover bg-center"
      :style="{
        backgroundImage: 'url(' + getImageUrl('Sign-in.png') + ')',
      }"
    >
      <!-- <VCol
        cols="0"
        sm="6"
        md="7"
        lg="9"
        class="bg-gray-100 hidden sm:flex justify-center max-h-screen"
      >
        <VImg
          :src="getImageUrl('auth_vector.png')"
          alt="Auth POS Image"
          max-height="90%"
        />
      </VCol> -->
      <VCol cols="12" sm="12" md="12" lg="12" class="flex align-middle">
        <main class="p-0 w-full flex justify-center align-middle items-center">
          <slot />
        </main>
      </VCol>
    </VRow>
  </div>
</template>
