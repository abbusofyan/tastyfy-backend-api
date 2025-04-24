<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
// import VCurrency from '@/Components/VCurrency.vue';
import Swal from 'sweetalert2';
import PopupLayout from '@/Layouts/PopupLayout.vue';
import FormBanner from './FormBanner.vue';

const page = usePage();

const props = defineProps({
  group: String,
});
const groupName = ref('');
if (props.group === 'featured_product') {
  groupName.value = 'Featured Product';
} else {
  groupName.value = 'Our Sponsor';
}
const title = ref(`Add ${groupName.value} Banner`);
const emits = defineEmits(['product-updated']);

const successMsg = () => {
  emits('data-updated');
  Swal.fire({
    icon: 'success',
    title: 'Success',
    text: 'Create a banner successfully',
    confirmButtonColor: 'primary',
    showConfirmButton: true,
  }).then((result) => {
    if (result.isConfirmed) {
      emits('closeAdd');
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

const initData = {
  _method: 'POST',
  group: props.group,
  file: null,
  url: null,
  order: null,
  file_name: null,
};

const submitForm = async (data) => {
  const formData = new FormData();
  formData.append('_method', 'POST');
  formData.append('file', data.file);
  formData.append('url', data.url);
  formData.append('group', data.group);

  try {
    const response = await axios.post(
      route('banner-management.store'),
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
    errorMsg(e);
  }
};
</script>
<template>
  <PopupLayout :name="title" close="closeAdd">
    <div>
      <FormBanner :banner="initData" @submit-form="submitForm"> </FormBanner>
      <div class="flex w-full justify-center text-center">
        <VBtn
          variant="text"
          class="my-2"
          size="large"
          color="primary"
          @click="$emit('closeAdd')"
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
