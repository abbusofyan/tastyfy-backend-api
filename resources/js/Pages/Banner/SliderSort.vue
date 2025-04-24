<script setup>
import { ref, watch } from 'vue';
import { VueDraggableNext } from 'vue-draggable-next';

const props = defineProps({
  title: String,
  banners: {
    type: Array,
    default: () => [],
  },
  group: String,
});

const startBanners = ref(props.banners);
const currentBanners = ref([...props.banners]);

const showDraggable = ref(false);

const toggleSort = () => {
  showDraggable.value = !showDraggable.value;
};

watch(
  () => props.banners,
  (newBanners) => {
    startBanners.value = newBanners;
    currentBanners.value = newBanners;
  },
  { deep: true },
);

const emits = defineEmits(['add-banner']);
</script>

<template>
  <VCard v-if="!showDraggable" elevation="8" class="m-1 !p-5">
    <div class="flex py-[16px] px-[24px] items-center gap-[8px] self-stretch">
      <div class="text-[18px] font-medium flex-[1_0_0%] text-[#DE9A3C]">
        {{ props.title }}
      </div>
      <div class="flex flex-col items-start">
        <VBtn
          v-if="currentBanners.length >= 1"
          class="mr-3"
          :prepend-icon="'mdi-cursor-move'"
          color="primary"
          rounded="xl"
          size="large"
          variant="outlined"
          @click="toggleSort()"
          >Sort
        </VBtn>
      </div>
      <div class="flex flex-col items-start">
        <VBtn
          class="mr-3"
          :prepend-icon="'mdi-plus'"
          color="primary"
          rounded="xl"
          size="large"
          variant="flat"
          @click="$emit('addButton', props.group)"
          >Add Banner
        </VBtn>
      </div>
    </div>
    <div
      v-if="currentBanners.length === 0"
      class="text-center text-[#4B465C] text-[16px] font-normal"
    >
      No Banner Yet
    </div>
    <div>
      <VSheet
        class="mx-auto"
        elevation="2"
        max-width="100%"
        style="box-shadow: unset !important"
      >
        <VSlideGroup v-model="model" class="pa-4" selected-class="bg-success">
          <VSlideGroupItem
            v-for="(banner, index) in currentBanners"
            :key="index"
            v-slot="{ isSelected, toggle, selectedClass }"
          >
            <div>
              <VImg
                :class="['ma-4', selectedClass]"
                height="200"
                width="400"
                aspect-ratio="16/9"
                cover
                :src="banner.file_name"
              >
                <div class="flex justify-end">
                  <VBtn
                    prepend-icon="mdi-pencil"
                    color="primary"
                    variant="flat"
                    size="normal"
                    rounded="xl"
                    class="absolute top-1 right-0 py-2 px-4"
                    @click="$emit('editButton', banner)"
                  ></VBtn></div
              ></VImg>
            </div>
          </VSlideGroupItem>
        </VSlideGroup>
      </VSheet>
    </div>
  </VCard>
  <VCard v-if="showDraggable" elevation="8" class="m-1 !p-5">
    <div class="flex py-[16px] px-[24px] items-center gap-[8px] self-stretch">
      <div class="text-[18px] font-medium flex-[1_0_0%] text-[#DE9A3C]">
        Sort {{ props.title }}
      </div>
      <div class="flex items-start">
        <VBtn
          class="mr-3"
          color="primary"
          rounded="xl"
          size="large"
          variant="flat"
          @click="
            $emit('saveNewBanners', currentBanners);
            toggleSort();
          "
          >SAVE
        </VBtn>
      </div>
    </div>
    <VueDraggableNext v-model="currentBanners" class="py-4 px-6" tag="div">
      <div
        v-for="(banner, index) in currentBanners"
        :key="banner.id"
        class="flex items-center my-2"
      >
        <div class="flex w-full">
          <div class="flex w-3/4 mr-5">
            <div class="align-middle flex items-center">
              <VIcon>mdi-menu</VIcon>
            </div>

            <VImg
              :src="banner.file_name"
              height="120"
              width="200"
              aspect-ratio="16/9"
              cover
              rounded="xl"
              class="m-5 !max-w-[200px] align-middle flex items-center"
            ></VImg>
            <div class="content-center">
              <p>{{ banner.original_name }}</p>
              <p>{{ banner.url }}</p>
            </div>
          </div>

          <div class="flex w-1/4 justify-end ml-5 items-center">
            <VBtn
              icon
              @click="$emit('deleteBanner', currentBanners, index)"
              variant="tonal"
              color="red"
            >
              <VIcon>mdi-trash-can-outline</VIcon>
            </VBtn>
          </div>
        </div>
      </div>
    </VueDraggableNext>
  </VCard>
</template>
