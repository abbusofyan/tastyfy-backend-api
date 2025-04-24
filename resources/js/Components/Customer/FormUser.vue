<script setup>
// import { useForm } from '@inertiajs/vue3';
import { reactive, ref } from "vue";
import { VPhoneInput } from "v-phone-input";

const props = defineProps({
    user: Object,
    roleAvail: Object,
});

const form = reactive({
    _method: props.user._method,
    id: props.user.id,
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone,
    password: props.user.password,
    password_confirmation: props.user.password_confirmation,
    group: props.user.group,
    id_number: props.user.id_number,
});

const show1 = ref(false);

const rules = {
    required: (value) => !!value || "Required.",
    min: (v) => v.length >= 8 || "Min 8 characters",
    emailMatch: () => "The email and password you entered don't match",
    confirmPassword: (v) =>
        v === form.password || "Password and confirm password must match",
};
</script>
<template>
    <VCard variant="flat" class="">
        <VCardText>
            <div class="flex">
                <div class="flex w-1/2 m-2">
                    <VSelect
                        v-model="form.group"
                        :items="[
                            'Public Customer',
                            'Beneficiaries Customer',
                            'Co-Payment Customer',
                        ]"
                        label="Group"
                        readonly
                        variant="outlined"
                    ></VSelect>
                </div>

                <div class="flex w-1/2 m-2">
                    <VTextField
                        variant="outlined"
                        label="ID Number"
                        v-model="form.id_number"
                        class="block w-full"
                        required
                        autocomplete="username"
                    />
                </div>
            </div>
            <div class="flex">
                <div class="flex w-1/2 m-2">
                    <VTextField
                        name="name"
                        label="Name"
                        variant="outlined"
                        v-model="form.name"
                    ></VTextField>
                </div>

                <div class="flex w-1/2 m-2">
                    <VTextField
                        variant="outlined"
                        label="Email"
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="block w-full"
                        required
                        autocomplete="username"
                    />
                </div>
            </div>
            <div class="flex">
                <div class="flex w-full m-2">
                    <VPhoneInput
                        v-model="form.phone"
                        :default-country="'SG'"
                        class="w-full"
                        label="Phone Number"
                        variant="outlined"
                    ></VPhoneInput>
                </div>
            </div>

            <div class="flex">
                <div class="flex w-1/2 m-2">
                    <VTextField
                        v-model="form.password"
                        :rules="[rules.required, rules.min]"
                        :type="show1 ? 'text' : 'password'"
                        variant="outlined"
                        hint="At least 8 characters"
                        label="Password"
                        name="input-10-1"
                        counter
                        @click:append="show1 = !show1"
                    ></VTextField>
                </div>
                <div class="flex w-1/2 m-2">
                    <VTextField
                        v-model="form.password_confirmation"
                        :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
                        :rules="[rules.required, rules.confirmPassword]"
                        :type="show1 ? 'text' : 'password'"
                        variant="outlined"
                        label="Confirm Password"
                        name="input-10-2"
                        counter
                        @click:append="show1 = !show1"
                    ></VTextField>
                </div>
            </div>
        </VCardText>
        <div class="flex justify-center m-1 pl-5">
            <VBtn
                class="mr-3"
                variant="flat"
                size="large"
                color="primary"
                rounded="xl"
                @click="$emit('submitForm', form)"
                >Save Data</VBtn
            >
        </div>
    </VCard>
</template>
