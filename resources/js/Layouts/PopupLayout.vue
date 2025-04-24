<script setup>
import { ref } from 'vue';

const props = defineProps({
  name: String,
  close: String,
});

const closeFunc = ref('');
closeFunc.value = props.close;

const emits = defineEmits([]);

const closePopup = () => {
  emits(closeFunc.value);
};
</script>

<template>
  <VCard class="!p-[50px]">
    <div class="flex justify-between m-3 title-popup">
      <div class="w-11/12 justify-center text-center">
        <span class="text-h4 !font-semibold">{{ props.name }}</span>
      </div>
      <div>
        <VBtn color="primary" variant="text" size="xs" @click="closePopup"
          ><VIcon size="x-large">mdi-close</VIcon></VBtn
        >
      </div>
    </div>

    <main>
      <slot />
    </main>
  </VCard>
</template>
