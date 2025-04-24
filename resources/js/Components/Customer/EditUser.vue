<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import Swal from "sweetalert2";
import PopupLayout from "@/Layouts/PopupLayout.vue";
import FormUser from "./FormUser.vue";

const emits = defineEmits(["data-updated"]);

const props = defineProps({
    user: Object,
});
const page = usePage();

const title = ref(`Edit '${props.user.user.name}'`);

const initData = {
    _method: "PUT",
    id: props.user.id,
    group: props.user.roles[0].name,
    id_number: props.user.customer_id,
    name: props.user.user.name,
    email: props.user.user.email,
    phone: props.user.user.phone,
    password: props.user.user.password,
};

const successMsg = () => {
    emits("data-updated");
    Swal.fire({
        icon: "success",
        title: "Success",
        text: "User Updated successfully",
        confirmButtonColor: "primary",
    }).then(() => {
        emits("closeEdit");
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
        id: data.id,
        user_id: props.user.user.id,
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
            method: "put",
            url: route("customer.update"),
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
                    @click="$emit('closeEdit')"
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
