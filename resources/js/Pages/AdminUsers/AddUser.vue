<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
// import VCurrency from '@/Components/VCurrency.vue';
import Swal from 'sweetalert2';
import PopupLayout from '@/Layouts/PopupLayout.vue';
import FormUser from './FormUser.vue';

const title = ref('Add Account');
const emits = defineEmits(['data-updated']);
const page = usePage();
const props = defineProps({
  dialog: Boolean,
  roleAvail: Object,
});
// const notification = ref(null);

const successMsg = () => {
  emits('data-updated');
  Swal.fire({
    icon: 'success',
    title: 'Success',
    text: 'Create a user successfully',
    confirmButtonColor: 'primary',
  }).then(() => {
    emits('closeAdd');
    emits('data-updated');
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
  name: null,
  status: null,
  email: null,
  password: null,
  phone: null,
};

const submitForm = async (data) => {
  const form = {
    name: data.name,
    status: data.status,
    phone: data.phone,
    email: data.email,
    password: data.password,
    password_confirmation: data.password_confirmation,
  };

  try {
    const response = await axios({
      method: 'post',
      url: route('adminUser.store'),
      headers: {
        Authorization: `Bearer ${page.props.webToken}`,
      },
      data: form,
    });
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
      <FormUser
        :user="initData"
        :role-avail="props.roleAvail"
        @submit-form="submitForm"
      >
      </FormUser>
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
