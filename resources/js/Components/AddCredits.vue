<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
// import VCurrency from '@/Components/VCurrency.vue';
import Swal from 'sweetalert2';
import PopupLayout from '@/Layouts/PopupLayout.vue';

const emits = defineEmits(['product-updated']);

const props = defineProps({
  user: Boolean,
});
console.log(props.user);

const title = ref('Add Credit');

const form = useForm({
  _method: 'PATCH',
  id: props.user.customer_id,
  credit: ref(0),
});

const notification = ref(null);
const initialCredit = ref(parseFloat(props.user.credit_balance).toFixed(2));
const addedCredit = ref(0);

// const success = () => {
//   notification.value = {
//     type: 'success',
//     message: 'This user credit added successfully.',
//   };

//   //   initialCredit.value += addedCredit.value.value;
//   emits('data-updated');
//   setTimeout(() => {
//     notification.value = null;
//   }, 10000); // Hide after 10 seconds
// };

// const error = (errors) => {
//   notification.value = {
//     type: 'error',
//     message: errors.response.data.data.error.message,
//   };
//   setTimeout(() => {
//     notification.value = null;
//   }, 10000); // Hide after 10 seconds
// };

const successMsg = () => {
  addedCredit.value = ref(parseFloat(form.credit));
  initialCredit.value = parseFloat(initialCredit.value).toFixed(2);
  initialCredit.value = parseFloat(initialCredit.value) + parseFloat(addedCredit.value.value);
  emits('data-updated');
  Swal.fire({
    icon: 'success',
    title: 'Success',
    text: 'Add credits successfully',
    confirmButtonColor: 'primary',
  }).then(() => {
    emits('closeCredit');
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

const submitForm = async () => {
  try {
    const response = await axios({
      method: 'post',
      url: route('customer.addCredit'),
      data: {
        customer_id: props.user.customer_id,
        amount: form.credit,
      },
    });

    if (response.status === 200) {
      console.log(response);
      successMsg();
    }
  } catch (e) {
    console.error(e);
    errorMsg(e);
  }
  // form.post(route('beneficiariesUser.addCredit'), {
  //   onSuccess: () => {
  //     success();
  //   },
  //   onError: (errors) => {
  //     error(errors);
  //   },
  // });
};
</script>
<template>
  <PopupLayout :name="title" close="closeCredit">
    <div class="text-center mb-10">
      <p>
        <strong>ID Number : {{ props.user.customer_id }}</strong>
      </p>
      <p>
        <strong>Current Credit Amount : {{ initialCredit }} SGD</strong>
      </p>
    </div>
    <div>
      <VTextField
        v-model="form.credit"
        class="block w-full"
        label="Credit Amount"
        required
        type="number"
        variant="outlined"
      />
      <div class="flex justify-center m-2 pl-5">
        <VBtn
          :class="{ 'opacity-50': form.credit === 0 }"
          :readonly="form.credit === 0"
          class="mr-3"
          color="primary"
          rounded="xl"
          size="large"
          variant="flat"
          @click="submitForm"
          >Save Data
        </VBtn>
      </div>
      <div class="flex w-full justify-center text-center">
        <VBtn
          class="my-2"
          color="primary"
          size="large"
          variant="text"
          @click="$emit('CloseCredit')"
          >CANCEL
        </VBtn>
      </div>

      <div v-if="notification" class="my-5 fixed right-5 bottom-3 z-10">
        <VChip
          :color="notification.type === 'success' ? 'success' : 'error'"
          class="mx-2"
          outlined
          size="large"
        >
          {{ notification.message }}
        </VChip>
      </div>
    </div>
  </PopupLayout>
</template>
