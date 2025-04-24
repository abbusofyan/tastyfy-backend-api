<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
// import VCurrency from '@/Components/VCurrency.vue';
import Swal from "sweetalert2";
import PopupLayout from "@/Layouts/PopupLayout.vue";

const emits = defineEmits(["product-updated"]);

const title = ref("Import Credit");

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
    _method: "PATCH",
    files: null,
    //   userType: props.type,
});

const successMsg = () => {
    Swal.fire({
        icon: "success",
        title: "Success",
        text: "Import Credits successfully",
        confirmButtonColor: "primary",
    });
    emits("data-updated");
};

const submitForm = async () => {
    if (!form.files) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Please select a file first.",
            confirmButtonColor: "#dc3545",
        });
        return;
    }

    Swal.fire({
        title: "Processing...",
        text: "Please wait while we import the credits",
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });

    const formData = new FormData();
    formData.append("file", form.files);

    try {
        const response = await axios.post(
            "/customers/import-credit",
            formData,
            {
                headers: { "Content-Type": "multipart/form-data" },
            },
        );

        const errorList =
            response.data.errors.length > 0
                ? "<br><br>Errors:<br>" + response.data.errors.join("<br>")
                : "";

        Swal.fire({
            icon: response.data.errors.length > 0 ? "warning" : "success",
            title: "Import Complete",
            html: response.data.message + errorList,
            confirmButtonColor: "primary",
        });

        emits("data-updated");
        form.reset();
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                error.response?.data?.message ||
                "An error occurred during import.",
            confirmButtonColor: "#dc3545",
        });
    }
};
</script>
<template>
    <PopupLayout :name="title" close="closeImportCredit">
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
                    @click="$emit('CloseImportCredit')"
                    >CANCEL</VBtn
                >
            </div>
            <div class="flex w-full justify-center text-center">
                <VBtn
                    variant="outlined"
                    class="my-2"
                    size="small"
                    color="secondary"
                    href="/templates/import-credit-template.xlsx"
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
