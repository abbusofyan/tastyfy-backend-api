<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="flex justify-between mb-5 rounded-sm">
    <Link :href="route('publicUser.index')" class="w-1/3"
      ><VBtn
        color="primaryTr"
        size="x-large"
        :variant="route().current('publicUser.index') ? 'flat' : 'outlined'"
        class="w-full !rounded-l-lg !rounded-r-[0] !border-r-0"
        >PUBLIC</VBtn
      ></Link
    >
    <Link :href="route('beneficiariesUser.index')" class="w-1/3"
      ><VBtn
        color="primaryTr"
        size="x-large"
        :variant="
          route().current('beneficiariesUser.index') ? 'flat' : 'outlined'
        "
        rounded="0"
        class="w-full"
        >BENEFICIARIES</VBtn
      ></Link
    >
    <Link :href="route('coPaymentUser.index')" class="w-1/3"
      ><VBtn
        color="primaryTr"
        size="x-large"
        :variant="route().current('coPaymentUser.index') ? 'flat' : 'outlined'"
        class="w-full !rounded-l-[0] !rounded-r-lg !border-l-0"
        >CO-PAYMENT</VBtn
      ></Link
    >
  </div>
</template>
