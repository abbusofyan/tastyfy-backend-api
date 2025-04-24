<script setup>
import { ref } from "vue";
import AppLayout from "./AppLayout.vue";

const props = defineProps({
    name: String,
    icon: String,
    importCredit: {
        type: [Boolean, null],
        default: null,
    },
    importUser: {
        type: [Boolean, null],
        default: null,
    },
    hideButton: {
        type: [Boolean, false],
        default: false,
    },
});

const addItemFunc = ref("addItem");
const icons = props.icon;

const emits = defineEmits([]);

const addItemFunction = () => {
    emits(addItemFunc.value);
};

const importCreditFunction = () => {
    emits("openImportCredit");
};

const importUserFunction = () => {
    emits("openImportUser");
};
</script>

<template>
    <AppLayout :title="props.name">
        <div class="flex mb-8 pt-1">
            <h1 class="text-3xl font-semibold w-1/3">{{ props.name }}</h1>
            <div class="flex justify-end w-full">
                <VBtn
                    v-if="props.importUser"
                    :prepend-icon="icons"
                    variant="flat"
                    class="ml-5 mr-0 md:mx-5 add-item-button"
                    color="primary"
                    size="large"
                    @click="importUserFunction"
                >
                    Import User</VBtn
                >
                <VBtn
                    v-if="props.importCredit"
                    :prepend-icon="icons"
                    variant="flat"
                    class="ml-5 mr-0 md:mx-5 add-item-button"
                    color="primary"
                    size="large"
                    @click="importCreditFunction"
                >
                    Import Credit</VBtn
                >
                <VBtn
                    v-if="!props.hideButton"
                    :prepend-icon="icons"
                    variant="flat"
                    class="ml-5 mr-0 md:mx-5 add-item-button"
                    color="primary"
                    size="large"
                    @click="addItemFunction"
                >
                    Add {{ props.name }}</VBtn
                >
                <slot name="buttons-index"></slot>
            </div>
        </div>

        <main class="p-0 w-full">
            <slot />
        </main>
    </AppLayout>
</template>
