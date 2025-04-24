<script setup>
import { ref, watchEffect } from "vue";
import { Head, router } from "@inertiajs/vue3";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import Banner from "@/Components/Banner.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const drawer = ref(false);
// const rail = ref(true);

const navLinks = ref([
    //   {
    //     id: 'dashboard',
    //     icon: 'mdi-home-city',
    //     title: 'Dashboard',
    //     href: route('dashboard'),
    //     value: 'dashboard',
    //     parent: null,
    //   },

    {
        id: "user-management",
        icon: "mdi-cellphone",
        title: "App Users",
        href: null, // No route for parent
        value: "",
        parent: null,
    },
    {
        id: "public",
        icon: "mdi-circle-medium",
        title: "Public",
        href: route("publicUser.index"),
        value: "publicUser.index",
        parent: "user-management",
    },
    {
        id: "beneficiaries",
        icon: "mdi-circle-medium",
        title: "Beneficiaries",
        href: route("beneficiariesUser.index"),
        value: "beneficiariesUser.index",
        parent: "user-management",
    },
    {
        id: "co-payment",
        icon: "mdi-circle-medium",
        title: "Co-Payment",
        href: route("coPaymentUser.index"),
        value: "coPaymentUser.index",
        parent: "user-management",
    },
    {
        id: "admin-user",
        icon: "mdi-account-multiple-outline",
        title: "Admin Users",
        href: route("adminUser.index"),
        value: "adminUser.index",
        parent: null,
    },
    {
        id: "banner-management",
        icon: "mdi-image-multiple",
        title: "Banner Management",
        href: route("banner-management.index"),
        value: "banner-management.index",
        parent: null,
    },
    {
        id: "topup-log",
        icon: "mdi-cash-multiple",
        title: "Topup Logs",
        href: route("topup-log.index"),
        value: "topup-log.index",
        parent: null,
    },
    {
        id: "transaction-log",
        icon: "mdi-history",
        title: "Transaction Logs",
        href: route("transaction-log.index"),
        value: "transaction-log.index",
        parent: null,
    },
]);

const isChild = (parentId) =>
    navLinks.value.some((link) => link.parent === parentId);

const getChildren = (parentId) =>
    navLinks.value.filter((link) => link.parent === parentId);

const defaultExpandedPanels = ref([0]);

// watchEffect(() => {
//     // Detect viewport width and update drawer value accordingly
//     const isMobile = window.innerWidth <= 480;
//     drawer.value = !isMobile;
// });

const switchToTeam = (team) => {
    router.put(
        route("current-team.update"),
        {
            team_id: team.id,
        },
        {
            preserveState: false,
        },
    );
};

const logout = () => {
    router.post(route("logout"));
};
</script>

<template>
    <div class="bg-whitegray">
        <Head :title="title" />

        <Banner />

        <VCard class="!z-50">
            <VLayout>
                <VNavigationDrawer
                    class="!shadow-[1px_0px_25px_5px_rgba(0,0,0,0.1)] py-2"
                    v-model="drawer"
                    temporary
                >
                    <div class="flex">
                        <ApplicationMark
                            class="h-14 ml-12 p-2 pl-0 w-[80%]"
                        ></ApplicationMark>
                        <VBtn
                            class="my-5 mx-2 p-2 self-start"
                            color="transparent"
                            variant="flat"
                            size="super-small"
                            @click.stop="drawer = !drawer"
                        >
                            <VIcon
                                icon="mdi-close"
                                color="primary"
                                size="x-large"
                            />
                        </VBtn>
                    </div>

                    <VList density="compact" class="!p-0" nav>
                        <VExpansionPanels
                            class="w-full flex sidebar-menu"
                            v-model="defaultExpandedPanels"
                            flat
                        >
                            <template
                                v-for="navLink in navLinks"
                                :key="navLink.id"
                            >
                                <div class="w-full" v-if="!navLink.parent">
                                    <div v-if="isChild(navLink.id)">
                                        <VExpansionPanel>
                                            <VExpansionPanelTitle
                                                class="!p-2 !pr-10 !min-h-[48px] max-h-[48px] border-0 shadow-transparent"
                                            >
                                                <VListItem
                                                    :prepend-icon="navLink.icon"
                                                    :title="navLink.title"
                                                    class="w-full"
                                                ></VListItem>
                                            </VExpansionPanelTitle>
                                            <VExpansionPanelText class="p-0">
                                                <VList class="w-full" dense>
                                                    <template
                                                        v-for="childLink in getChildren(
                                                            navLink.id,
                                                        )"
                                                        :key="childLink.id"
                                                    >
                                                        <NavLink
                                                            :href="
                                                                childLink.href
                                                            "
                                                            :active="
                                                                route().current(
                                                                    childLink.value,
                                                                )
                                                            "
                                                            class="w-full"
                                                        >
                                                            <VListItem
                                                                :prepend-icon="
                                                                    childLink.icon
                                                                "
                                                                :title="
                                                                    childLink.title
                                                                "
                                                                class="pl-6"
                                                            ></VListItem>
                                                        </NavLink>
                                                    </template>
                                                </VList>
                                            </VExpansionPanelText>
                                        </VExpansionPanel>
                                    </div>
                                    <NavLink
                                        v-else
                                        :href="navLink.href"
                                        :active="route().current(navLink.value)"
                                        class="w-full"
                                    >
                                        <VListItem
                                            :prepend-icon="navLink.icon"
                                            :title="navLink.title"
                                        ></VListItem>
                                    </NavLink>
                                </div>
                            </template>
                        </VExpansionPanels>
                    </VList>
                </VNavigationDrawer>
            </VLayout>
        </VCard>

        <div class="min-h-screen bg-gray-100">
            <nav class="py-4 border-gray-100 fixed w-[100%] z-40 mx-0">
                <!-- Primary Navigation Menu -->
                <div
                    class="max-w-7xl bg-white mx-auto px-4 sm:px-6 lg:px-8 rounded-md shadow-md"
                >
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <VBtn
                                    class="my-auto mx-2 p-2"
                                    color="transparent"
                                    variant="flat"
                                    size="super-small"
                                    @click.stop="drawer = !drawer"
                                >
                                    <VIcon
                                        icon="mdi-menu"
                                        color="primary"
                                        size="x-large"
                                    />
                                </VBtn>
                            </div>
                        </div>
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <div class="ms-3 relative">
                                <!-- Teams Dropdown -->
                                <Dropdown
                                    v-if="$page.props.jetstream.hasTeamFeatures"
                                    align="right"
                                    width="60"
                                >
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150"
                                            >
                                                {{
                                                    $page.props.auth.user
                                                        .current_team.name
                                                }}

                                                <svg
                                                    class="ms-2 -me-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke-width="1.5"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="w-60">
                                            <!-- Team Management -->
                                            <div
                                                class="block px-4 py-2 text-xs text-gray-400"
                                            >
                                                Manage Team
                                            </div>

                                            <!-- Team Settings -->
                                            <DropdownLink
                                                :href="
                                                    route(
                                                        'teams.show',
                                                        $page.props.auth.user
                                                            .current_team,
                                                    )
                                                "
                                            >
                                                Team Settings
                                            </DropdownLink>

                                            <DropdownLink
                                                v-if="
                                                    $page.props.jetstream
                                                        .canCreateTeams
                                                "
                                                :href="route('teams.create')"
                                            >
                                                Create New Team
                                            </DropdownLink>

                                            <!-- Team Switcher -->
                                            <template
                                                v-if="
                                                    $page.props.auth.user
                                                        .all_teams.length > 1
                                                "
                                            >
                                                <div
                                                    class="border-t border-gray-200"
                                                />

                                                <div
                                                    class="block px-4 py-2 text-xs text-gray-400"
                                                >
                                                    Switch Teams
                                                </div>

                                                <template
                                                    v-for="team in $page.props
                                                        .auth.user.all_teams"
                                                    :key="team.id"
                                                >
                                                    <form
                                                        @submit.prevent="
                                                            switchToTeam(team)
                                                        "
                                                    >
                                                        <DropdownLink
                                                            as="button"
                                                        >
                                                            <div
                                                                class="flex items-center"
                                                            >
                                                                <svg
                                                                    v-if="
                                                                        team.id ==
                                                                        $page
                                                                            .props
                                                                            .auth
                                                                            .user
                                                                            .current_team_id
                                                                    "
                                                                    class="me-2 h-5 w-5 text-green-400"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    fill="none"
                                                                    viewBox="0 0 24 24"
                                                                    stroke-width="1.5"
                                                                    stroke="currentColor"
                                                                >
                                                                    <path
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                                    />
                                                                </svg>

                                                                <div>
                                                                    {{
                                                                        team.name
                                                                    }}
                                                                </div>
                                                            </div>
                                                        </DropdownLink>
                                                    </form>
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button
                                            v-if="
                                                $page.props.jetstream
                                                    .managesProfilePhotos
                                            "
                                            type="button"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150"
                                        >
                                            <img
                                                class="h-8 w-8 rounded-full object-cover mx-2"
                                                :src="
                                                    $page.props.auth.user
                                                        .profile_photo_url
                                                "
                                                :alt="
                                                    $page.props.auth.user.name
                                                "
                                            />
                                            {{ $page.props.auth.user.name }}

                                            <svg
                                                class="ms-2 -me-0.5 h-4 w-4"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke-width="1.5"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                                                />
                                            </svg>
                                        </button>

                                        <span
                                            v-else
                                            class="inline-flex rounded-md"
                                        >
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="ms-2 -me-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke-width="1.5"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div
                                            class="block px-4 py-2 text-xs text-gray-400"
                                        >
                                            Manage Account
                                        </div>

                                        <DropdownLink
                                            :href="route('profile.show')"
                                        >
                                            Profile
                                        </DropdownLink>

                                        <DropdownLink
                                            v-if="
                                                $page.props.jetstream
                                                    .hasApiFeatures
                                            "
                                            :href="route('api-tokens.index')"
                                        >
                                            API Tokens
                                        </DropdownLink>

                                        <div class="border-t border-gray-200" />

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="flex items-center px-4">
                            <div
                                v-if="
                                    $page.props.jetstream.managesProfilePhotos
                                "
                                class="shrink-0 me-3"
                            >
                                <img
                                    class="h-10 w-10 rounded-full object-cover"
                                    :src="
                                        $page.props.auth.user.profile_photo_url
                                    "
                                    :alt="$page.props.auth.user.name"
                                />
                            </div>

                            <div>
                                <div
                                    class="font-medium text-base text-gray-800"
                                >
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink
                                :href="route('profile.show')"
                                :active="route().current('profile.show')"
                            >
                                Profile
                            </ResponsiveNavLink>

                            <ResponsiveNavLink
                                v-if="$page.props.jetstream.hasApiFeatures"
                                :href="route('api-tokens.index')"
                                :active="route().current('api-tokens.index')"
                            >
                                API Tokens
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    Log Out
                                </ResponsiveNavLink>
                            </form>

                            <!-- Team Management -->
                            <template
                                v-if="$page.props.jetstream.hasTeamFeatures"
                            >
                                <div class="border-t border-gray-200" />

                                <div
                                    class="block px-4 py-2 text-xs text-gray-400"
                                >
                                    Manage Team
                                </div>

                                <!-- Team Settings -->
                                <ResponsiveNavLink
                                    :href="
                                        route(
                                            'teams.show',
                                            $page.props.auth.user.current_team,
                                        )
                                    "
                                    :active="route().current('teams.show')"
                                >
                                    Team Settings
                                </ResponsiveNavLink>

                                <ResponsiveNavLink
                                    v-if="$page.props.jetstream.canCreateTeams"
                                    :href="route('teams.create')"
                                    :active="route().current('teams.create')"
                                >
                                    Create New Team
                                </ResponsiveNavLink>

                                <!-- Team Switcher -->
                                <template
                                    v-if="
                                        $page.props.auth.user.all_teams.length >
                                        1
                                    "
                                >
                                    <div class="border-t border-gray-200" />

                                    <div
                                        class="block px-4 py-2 text-xs text-gray-400"
                                    >
                                        Switch Teams
                                    </div>

                                    <template
                                        v-for="team in $page.props.auth.user
                                            .all_teams"
                                        :key="team.id"
                                    >
                                        <form
                                            @submit.prevent="switchToTeam(team)"
                                        >
                                            <ResponsiveNavLink as="button">
                                                <div class="flex items-center">
                                                    <svg
                                                        v-if="
                                                            team.id ==
                                                            $page.props.auth
                                                                .user
                                                                .current_team_id
                                                        "
                                                        class="me-2 h-5 w-5 text-green-400"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke-width="1.5"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                        />
                                                    </svg>
                                                    <div>{{ team.name }}</div>
                                                </div>
                                            </ResponsiveNavLink>
                                        </form>
                                    </template>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="z-10">
                <div class="max-w-7xl mx-auto py-3 px-0 sm:px-6 lg:px-8 z-10">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="max-w-7xl pt-20 mx-auto flex justify-center z-1">
                <VCard class="pa-3 my-2 w-full" variant="text">
                    <slot />
                </VCard>
            </main>
        </div>
    </div>
</template>
<style>
.sidebar-menu .v-expansion-panel-text__wrapper {
    padding: 0;
}
.sidebar-menu .v-expansion-panel-text__wrapper > .v-list {
    padding: 0;
}
.v-expansion-panel .v-expansion-panel__shadow {
    box-shadow: unset;
}
</style>
