<script setup>
const logoUrl = import.meta.env.VITE_APP_LOGO;
// const titleApp = import.meta.env.VITE_APP_NAME;
</script>

<template>
    <Link
        :href="'/'"
        class="flex align-middle h-[100px] mb-1 p-1 !pl-2 justify-center"
    >
        <VImg
            src="/site_images/taste-asia-logo.png"
            alt="Logo"
            class="h-[80px] p-1"
        />
        <!-- <span class="text-md pl-3 pt-2 !font-bold !font-poppins font">{{
      titleApp
    }}</span> -->
    </Link>
</template>
