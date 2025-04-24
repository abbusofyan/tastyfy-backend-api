<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
// import VCurrency from '@/Components/VCurrency.vue';
import Swal from "sweetalert2";
import PopupLayout from "@/Layouts/PopupLayout.vue";

const emits = defineEmits(["product-updated"]);

const title = ref("Import User");

const props = defineProps({
    type: String,
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

const form = useForm({
    _method: "POST",
    files: [],
    userType: props.type,
});

const successMsg = () => {
    Swal.fire({
        icon: "success",
        title: "Success",
        text: "Import Users successfully",
        confirmButtonColor: "primary",
    });
    emits("data-updated");
};

const submitForm = async () => {
    let url;
    if (props.type == "Co-Payment") {
        url = route("coPaymentUser.import");
    } else {
        url = route("beneficiariesUser.import");
    }
    Swal.fire({
        title: "Processing...",
        html: "Please wait while we import the users",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });

    try {
        const formData = new FormData();
        formData.append("_method", "POST");
        formData.append("userType", form.userType);
        form.files.forEach((file) => {
            formData.append("files[]", file);
        });

        await axios.post(url, formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });

        Swal.close();
        successMsg();
        $emit("CloseImportUser");
    } catch (error) {
        // console.log(error.response.data.data.error.message);
        Swal.close();
        if (error.response && error.response.data) {
            const validationErrors = error.response.data.data.error.message;
            const errorMessages = error.response.data.data.error.message;
            // .join("\n");

            Swal.fire({
                icon: "error",
                title: "Validation Error",
                text: errorMessages,
                confirmButtonColor: "#dc3545",
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "An error occurred during submission.",
                confirmButtonColor: "#dc3545",
            });
        }
    }
};
</script>
<template>
    <PopupLayout :name="title" close="closeImportUser">
        <div class="my-10">
            <VFileInput
                v-model="form.files"
                :show-size="1000"
                color="primary"
                label="File input"
                placeholder="Select your files"
                prepend-icon="mdi-paperclip"
                variant="outlined"
                counter
            >
                <template v-slot:selection="{ fileNames }">
                    <template
                        v-for="(fileName, index) in fileNames"
                        :key="fileName"
                    >
                        <VChip
                            v-if="index < 2"
                            class="me-2"
                            color="primary"
                            size="small"
                            label
                        >
                            {{ fileName }}
                        </VChip>

                        <span
                            v-else-if="index === 2"
                            class="text-overline text-grey-darken-3 mx-2"
                        >
                            +{{ files.length - 2 }} File(s)
                        </span>
                    </template>
                </template>
            </VFileInput>
            <div class="flex justify-center m-2 pl-5">
                <VBtn
                    class="mr-3"
                    variant="flat"
                    size="large"
                    color="primary"
                    rounded="xl"
                    @click="submitForm"
                    >Save Data</VBtn
                >
            </div>
            <div class="flex w-full justify-center text-center">
                <VBtn
                    variant="text"
                    class="my-2"
                    size="large"
                    color="primary"
                    @click="$emit('CloseImportUser')"
                    >CANCEL</VBtn
                >
            </div>
            <div class="flex w-full justify-center text-center">
                <VBtn
                    variant="outlined"
                    class="my-2"
                    size="small"
                    color="secondary"
                    href="/templates/import-user-template.xlsx"
                    download
                    prepend-icon="mdi-download"
                >
                    Download Example Template
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
