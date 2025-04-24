<script setup>
import { ref } from "vue";
// import VCurrency from '@/Components/VCurrency.vue';
import Swal from "sweetalert2";
import { usePage } from "@inertiajs/vue3";
import PopupLayout from "@/Layouts/PopupLayout.vue";
import FormUser from "./FormUser.vue";

const title = ref("Add Account");
const emits = defineEmits(["product-updated"]);
const page = usePage();
const props = defineProps({
    groupUser: String,
});
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

const initData = {
    _method: "POST",
    name: null,
    group: `${props.groupUser} Customer`,
    id_number: null,
    email: null,
    password: null,
    password_confirmation: null,
    phone: null,
};

const successMsg = () => {
    emits("data-updated");
    Swal.fire({
        icon: "success",
        title: "Success",
        text: "Create a user successfully",
        confirmButtonColor: "primary",
    }).then(() => {
        emits("closeAdd");
        emits("data-updated");
    });
};

const errorMsg = (errors) => {
    Swal.fire({
        icon: "error",
        title: "Error",
        text: errors.response.data.data.error.message,
        confirmButtonColor: "primary",
    });
};

const submitForm = async (data) => {
    const form = {
        name: data.name,
        role: data.group,
        customer_id: data.id_number,
        email: data.email,
        phone: data.phone,
        password: data.password,
        password_confirmation: data.password_confirmation,
    };

    try {
        const response = await axios({
            method: "post",
            url: route("customer.store"),
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

    //   console.log(response);
    // form.post(route("api.web.customer.store"), {
    //     onSuccess: () => {
    //         success();
    //     },
    //     onError: (errors) => {
    //         error(errors);
    //     },
    // });
};
</script>
<template>
    <PopupLayout :name="title" close="closeAdd">
        <div>
            <FormUser
                :role-avail="props.roleAvail"
                :user="initData"
                @submit-form="submitForm"
            >
            </FormUser>
            <div class="flex w-full justify-center text-center">
                <VBtn
                    class="my-2"
                    color="primary"
                    size="large"
                    variant="text"
                    @click="$emit('closeAdd')"
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
