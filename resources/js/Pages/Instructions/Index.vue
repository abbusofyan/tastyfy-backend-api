<script setup>
// import AppLayout from "@/Layouts/AppLayout.vue";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import { Head } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Instructions" />
    <div>
        <v-container>
            <div class="flex center items-center">
                <ApplicationMark class="w-32" />
                <h1 class="text-3xl font-semibold w-1/3">Instructions</h1>
            </div>

            <div class="py-12">
                <h2 class="text-2xl font-semibold">Account Deletion</h2>
                <p>
                    The account deletion request (including account and all
                    associated data)
                </p>
                <div class="py-5">
                    <h3 class="text-xl">
                        1. Click the user icon in the top-right corner
                    </h3>
                    <img
                        src="/instructions_images/deactive_account_1.jpg"
                        width="300px"
                        class="my-3"
                    />
                    <h3 class="text-xl font-semibold mt-5">
                        2.1. If not logged in
                    </h3>
                    <div class="pl-10 my-3">
                        <h3 class="text-xl">
                            2.1.1. Click "LOGIN" to go to the login screen
                        </h3>
                        <img
                            src="/instructions_images/deactive_account_2.jpg"
                            width="300px"
                            class="my-3"
                        />
                        <h3 class="text-xl">2.1.2. Log into your account</h3>
                        <img
                            src="/instructions_images/deactive_account_3.PNG"
                            width="300px"
                            class="my-3"
                        />
                        <h3 class="text-xl">
                            2.1.3. After successfully logging in, return to step
                            1
                        </h3>
                    </div>
                    <h3 class="text-xl font-semibold mt-5">
                        2.2. If already logged in
                    </h3>
                    <div class="pl-10 my-3">
                        <h3 class="text-xl">2.2.1. Click "DELETE ACCOUNT"</h3>
                        <img
                            src="/instructions_images/deactive_account_4_rev.jpg"
                            width="300px"
                            class="my-3"
                        />
                        <h3 class="text-xl">
                            2.2.2. Confirm deletion of the account.
                        </h3>
                        <img
                            src="/instructions_images/deactive_account_5_rev.jpg"
                            width="300px"
                            class="my-3"
                        />
                    </div>
                </div>
            </div>
        </v-container>
    </div>
</template>
