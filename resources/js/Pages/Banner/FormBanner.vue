<script setup>
// import { useForm } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

const props = defineProps({
  banner: Object,
});

const form = reactive({
  _method: props.banner._method,
  id: props.banner.id,
  file: props.banner.file,
  url: props.banner.url,
  group: props.banner.group,
  file_name: props.banner.file_name,
});

const rules = {
  required: (value) => !!value || 'Required.',
  url: (value) => {
    const pattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
    return pattern.test(value) || 'Invalid URL format.';
  },

  //   min: (v) => v.length >= 8 || 'Min 8 characters',
  //   emailMatch: () => "The email and password you entered don't match",
  //   confirmPassword: (v) => v === form.password || 'Password and confirm password must match',
};

const photoPreview = ref(null);
const photoInput = ref(null);

if (form.file_name) {
  photoPreview.value = form.file_name;
}

const selectNewPhoto = () => {
  photoInput.value.click();
};

const updatePhotoPreview = () => {
  const photo = photoInput.value.files[0];
  form.file = photo;

  if (!photo) return;

  const reader = new FileReader();

  reader.onload = (e) => {
    photoPreview.value = e.target.result;
  };

  reader.readAsDataURL(photo);
};
</script>
<template>
  <VCard variant="flat" class="">
    <VCardText>
      <div>
        <input
          id="photo"
          ref="photoInput"
          type="file"
          class="hidden"
          @change="updatePhotoPreview"
        />

        <div
          v-show="!photoPreview"
          class="mt-2 text-center flex justify-center my-5"
        >
          <div
            class="bg-bordergray rounded-md h-40 w-40 flex justify-center align-center"
          >
            <span>NO IMAGE</span>
          </div>
        </div>

        <div v-show="photoPreview" class="mt-2 flex justify-center">
          <VImg
            :src="photoPreview"
            class="block rounded-lg h-auto !max-h-[300px] w-full mb-3"
          ></VImg>
        </div>

        <div class="flex justify-center">
          <VBtn
            variant="outlined"
            color="primary"
            class="mt-2 me-2"
            type="button"
            @click.prevent="selectNewPhoto"
          >
            Select A New Photo
          </VBtn>
        </div>
      </div>
      <div class="flex my-5">
        <VTextField
          name="url"
          label="URL"
          variant="outlined"
          v-model="form.url"
          :rules="[rules.required, rules.url]"
        ></VTextField>
      </div>
    </VCardText>
    <div class="flex justify-center m-1 pl-5">
      <VBtn
        class="mr-3"
        variant="flat"
        size="large"
        color="primary"
        rounded="xl"
        @click="$emit('submitForm', form)"
        >Save Data</VBtn
      >
    </div>
  </VCard>
</template>
