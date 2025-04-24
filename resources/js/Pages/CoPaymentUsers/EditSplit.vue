<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
// import VCurrency from '@/Components/VCurrency.vue';
import Swal from 'sweetalert2';
import PopupLayout from '@/Layouts/PopupLayout.vue';

const emits = defineEmits(['product-updated']);

const props = defineProps({
  user: Boolean,
});

const page = usePage();
const title = ref('Adjust Cash / Credit Split');

// const notification = ref(null);

// const success = () => {
//   notification.value = {
//     type: 'success',
//     message: 'user added successfully.',
//   };
//   emits('data-updated');
//   setTimeout(() => {
//     notification.value = null;
//   }, 10000); // Hide after 10 seconds
// };

// const error = (errors) => {
//   notification.value = { type: 'error', message: errors.message };
//   setTimeout(() => {
//     notification.value = null;
//   }, 10000); // Hide after 10 seconds
// };

const form = useForm({
  _method: 'PATCH',
  id: props.user.id,
  credit_split: props.user.credit_split,
  cash_split: props.user.cash_split,
});

const splitCredit = ref(parseFloat(props.user.credit_split).toFixed(2));
const splitCash = ref(parseFloat(props.user.cash_split).toFixed(2));

const successMsg = () => {
  splitCredit.value = ref(parseFloat(form.credit_split));
  splitCash.value = ref(parseFloat(form.cash_split));
  emits('data-updated');
  Swal.fire({
    icon: 'success',
    title: 'Success',
    text: 'Edit cash and credit split of this user successfully',
    confirmButtonColor: 'primary',
  }).then(() => {
    emits('closeSplit');
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
      url: route('customer.splitCredit'),
      headers: {
        Authorization: `Bearer ${page.props.webToken}`,
      },
      data: {
        customer_id: props.user.customer_id,
        cash_split: form.cash_split,
        credit_split: form.credit_split,
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
  //   form.post(route('users.store'), {
  //     onSuccess: () => {
  //       success();
  //     },
  //     onError: (errors) => {
  //       error(errors);
  //     },
  //   });
};

watch(
  () => form.cash_split,
  (newVal) => {
    if (newVal !== null) {
      form.credit_split = (100 - parseFloat(newVal)).toFixed(2);
    }
  },
);

watch(
  () => form.credit_split,
  (newVal) => {
    if (newVal !== null) {
      form.cash_split = (100 - parseFloat(newVal)).toFixed(2);
    }
  },
);
</script>
<template>
  <PopupLayout :name="title" close="closeSplit">
    <div class="text-center mb-10">
      <p>
        <strong>ID Number : {{ props.user.customer_id }}</strong>
      </p>
      <p>
        <strong
          >Current Cash / Credit Split : {{ splitCash }} /
          {{ splitCredit }}</strong
        >
      </p>
    </div>
    <div>
      <div class="flex">
        <div class="flex w-1/2 m-2">
          <VTextField
            v-model="form.cash_split"
            class="block w-full"
            label="Cash Split Portion"
            required
            type="number"
            variant="outlined"
          />
        </div>

        <div class="flex w-1/2 m-2">
          <VTextField
            v-model="form.credit_split"
            class="block w-full"
            label="Credit Split Portion"
            required
            type="number"
            variant="outlined"
          />
        </div>
      </div>
      <div class="flex justify-center m-2 pl-5">
        <VBtn
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
          @click="$emit('CloseSplit')"
          >CANCEL
        </VBtn>
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
