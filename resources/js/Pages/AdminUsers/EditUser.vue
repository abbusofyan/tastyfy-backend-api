<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';
import PopupLayout from '@/Layouts/PopupLayout.vue';
import FormUser from './FormUser.vue';

const emits = defineEmits(['data-updated']);

const props = defineProps({
  user: Object,
});
const page = usePage();
const title = ref(`Edit '${props.user.name}'`);

const initData = {
  _method: 'PUT',
  id: props.user.id,
  status: props.user.is_active,
  phone: props.user.phone,
  name: props.user.name,
  email: props.user.email,
  password: props.user.password,
};

const successMsg = () => {
  emits('data-updated');
  Swal.fire({
    icon: 'success',
    title: 'Success',
    text: 'Updated user data successfully',
    confirmButtonColor: 'primary',
  }).then(() => {
    emits('closeEdit');
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

const submitForm = async (data) => {
  const form = {
    id: data.id,
    name: data.name,
    status: data.status,
    phone: data.phone,
    email: data.email,
    password: data.password,
    password_confirmation: data.password_confirmation,
  };

  try {
    const response = await axios({
      method: 'put',
      url: route('adminUser.update'),
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

// console.log(form);
</script>
<template>
  <PopupLayout :name="title" close="closeEdit">
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
