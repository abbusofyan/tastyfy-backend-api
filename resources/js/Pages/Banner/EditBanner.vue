<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';
import PopupLayout from '@/Layouts/PopupLayout.vue';
import FormBanner from './FormBanner.vue';

const page = usePage();

const emits = defineEmits(['data-updated']);

const props = defineProps({
  banner: Object,
});

const groupName = ref('');
if (props.group === 'featured_product') {
  groupName.value = 'Featured Product';
} else {
  groupName.value = 'Our Sponsor';
}
const title = ref(`Edit ${groupName.value} Banner`);

const initData = {
  _method: 'PATCH',
  id: props.banner.id,
  group: props.banner.group,
  file: props.banner.file,
  url: props.banner.url,
  file_name: props.banner.file_name,
};

const successMsg = () => {
  emits('data-updated');
  Swal.fire({
    icon: 'success',
    title: 'Success',
    text: 'Edit banner successfully',
    confirmButtonColor: 'primary',
    showConfirmButton: true,
  }).then((result) => {
    if (result.isConfirmed) {
      emits('closeEdit');
      emits('data-updated');
    }
  });
};

const errorMsg = (errors) => {
  Swal.fire({
    icon: 'error',
    title: 'Error',
    text: errors.response.data.data.error.message,
    confirmButtonColor: 'primary',
  });
};

// const successMsg = () => {
//   Swal.fire({
//     icon: 'success',
//     title: 'Success',
//     text: 'update banner data successfully',
//     confirmButtonColor: 'primary',
//   });
//   emits('data-updated');
// };

const submitForm = async (data) => {
  const formData = new FormData();
  formData.append('_method', data._method);
  formData.append('id', data.id);
  formData.append('file', data.file);
  formData.append('url', data.url);
  formData.append('group', data.group);

  try {
    const response = await axios.post(
      route('banner-management.update'),
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data',
          Authorization: `Bearer ${page.props.webToken}`,
        },
      },
    );

    console.log(response);
    successMsg();
  } catch (e) {
    console.error(e);
    // errorMsg(e);
  }
};

// console.log(form);
</script>
<template>
  <PopupLayout :name="title" close="closeEdit">
    <div>
      <FormBanner :banner="initData" @submit-form="submitForm"> </FormBanner>
      <div class="flex w-full justify-center text-center">
        <VBtn
          variant="text"
          class="my-2"
          size="large"
          color="primary"
          @click="$emit('closeEdit')"
          >CANCEL</VBtn
        >
      </div>

      <!-- <div class="my-5 fixed right-5 bottom-3 z-10" v-if="notification">
        <VChip
          :color="notification.type === 'success' ? 'success' : 'error'"
          outlined
          size="large"
          class="mx-2"
        >
          {{ notification.message }}
        </VChip>
      </div> -->
    </div>
  </PopupLayout>
</template>
