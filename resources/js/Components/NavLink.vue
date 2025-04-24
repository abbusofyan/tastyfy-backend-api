<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  href: String,
  active: Boolean,
});

const classes = computed(() => (props.active
  ? 'w-full max-h-[48px] active inline-flex bg-white items-center p-1 pl-2 border-r-4 border-solid border-secondary text-md font-medium leading-5 text-primary focus:outline-none focus:border-secondary transition duration-150 ease-in-out'
  : 'w-full max-h-[48px] inactive inline-flex items-center p-1 pl-2 border-secondary text-md font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out'));
</script>

<template>
  <Link :href="href" :class="classes">
    <slot />
  </Link>
</template>
<style>
.border-primary {
  border-color: #00a65a;
}
.v-list-item-title {
  font-size: 14px !important;
  font-weight: 700 !important;
}
</style>
