<script setup>
import { ref, watch } from "vue";
import IndexLayout from "@/Layouts/IndexLayout.vue";
import CustomerTab from "@/Components/CustomerTab.vue";
import AddUser from "@/Components/Customer/AddUser.vue";
import EditUser from "@/Components/Customer/EditUser.vue";

// Dummy data
// const dummyData = ref([
//   {
//     id: 1,
//     name: 'Dummy Product 1',
//     description: 'Description of Dummy Product 1',
//   },
//   {
//     id: 2,
//     name: 'Dummy Product 2',
//     description: 'Description of Dummy Product 2',
//   },
//   {
//     id: 3,
//     name: 'Dummy Product 3',
//     description: 'Description of Dummy Product 3',
//   },
// ]);

const props = defineProps({
    users: Object,
});

const headers = [
    { title: "NAME", value: "user.name", sortable: true },
    { title: "ID Number", value: "customer_id", sortable: true },
    { title: "PHONE", value: "user.phone", sortable: true },
    { title: "EMAIL", value: "user.email", sortable: true },
    { title: "ACTIONS", value: "actions", sortable: false },
];

const selectedUser = ref(null);
// const dialogView = ref(false);
const dialogEdit = ref(false);
const dialogAdd = ref(false);
const chipSearch = ref(false);
const chipSearchText = ref("");
const itemsPerPage = ref(10);
const loading = ref(false);
const search = ref("");
const searchVal = ref(search.value);
const currentPage = ref(1);
const userList = ref(props.users.data || []); // Initialize as array from props
const totaluser = ref(props.users.total || 0);

// Watch the props.users to update userList
watch(
    () => props.users,
    (newUsers) => {
        userList.value = newUsers.data || [];
        totaluser.value = newUsers.total || 0;
    },
    { immediate: true },
);

const loadItems = async ({ page, itemsPerPage, sortBy, search }) => {
    loading.value = true;

    search = searchVal.value;

    try {
        const response = await axios.get(route("publicUser.fetchData"), {
            params: {
                page,
                itemsPerPage,
                sortBy: sortBy[0]?.key,
                sortOrder: sortBy[0]?.order,
                search,
            },
        });

        userList.value = response.data.users.data;
        totaluser.value = response.data.users.total;
        currentPage.value = response.data.users.current_page;
    } catch (error) {
        console.error("Error fetching users:", error);
    }

    loading.value = false;
};

const searchOnEnter = () => {
    searchVal.value = search.value;
    if (searchVal.value) {
        chipSearch.value = true;
    } else {
        chipSearch.value = false;
    }
    chipSearchText.value = searchVal.value;
    loadItems({
        page: 1,
        itemsPerPage: itemsPerPage.value,
        sortBy: [],
        search: search.value,
    });
};

const closeSearchChip = () => {
    searchVal.value = "";
    search.value = "";
    chipSearch.value = false;
    loadItems({
        page: 1,
        itemsPerPage: itemsPerPage.value,
        sortBy: [],
        search: search.value,
    });
};

const dataUpdate = () => {
    loadItems({
        page: currentPage.value,
        itemsPerPage: itemsPerPage.value,
        sortBy: [],
        search: search.value,
    });
};

const addItem = () => {
    // Open the dialog
    dialogAdd.value = true;
};

// const viewItem = (item) => {
//   selectedUser.value = item;
//   // Open the dialog
//   dialogView.value = true;
// };

const editItem = (item) => {
    selectedUser.value = item;
    // Open the dialog
    dialogEdit.value = true;
};

// const closeEdit = () => {
//   dialogEdit.value = false;
// };

const closeAdd = () => {
    dialogAdd.value = false;
};

const closeEdit = () => {
    dialogEdit.value = false;
};
</script>

<template>
    <IndexLayout name="Public Users" @add-item="addItem" icon="mdi-plus">
        <CustomerTab />
        <VCard elevation="8" class="m-1 !p-5">
            <div class="flex justify-around">
                <div class="w-2/3" />
                <VTextField
                    v-model="search"
                    prepend-inner-icon="mdi-magnify"
                    label="Search"
                    clearable
                    variant="outlined"
                    class="w-1/3"
                    hint="type keyword here and press enter to search data"
                    @keydown.enter="searchOnEnter"
                ></VTextField>
            </div>
            <VChip
                v-if="chipSearch"
                class="ma-2"
                closable
                @click:close="closeSearchChip"
            >
                Search for '{{ chipSearchText }}'
            </VChip>
            <VDataTableServer
                v-model:items-per-page="itemsPerPage"
                :headers="headers"
                :items="userList"
                :items-length="totaluser"
                :loading="loading"
                item-value="id"
                @update:options="loadItems"
                item-key="id"
                class="table-data borderless-table"
            >
                <template v-slot:item.actions="{ item }">
                    <div class="table-action flex">
                        <VBtn
                            class="p-1 m-1"
                            size="regular"
                            variant="text"
                            color="primary"
                            rounded="lg"
                            text
                            @click="editItem(item)"
                        >
                            <VIcon size="24">mdi-pencil-outline</VIcon> EDIT
                        </VBtn>
                    </div>
                </template>
            </VDataTableServer>
            <VDialog
                v-model="dialogAdd"
                max-width="800px"
                persistent
                z-index="1000"
            >
                <AddUser
                    group-user="Public"
                    @close-add="closeAdd"
                    @data-updated="dataUpdate"
                ></AddUser
            ></VDialog>
            <VDialog
                v-model="dialogEdit"
                z-index="1000"
                max-width="800px"
                persistent
            >
                <EditUser
                    :role="Public"
                    :user="selectedUser"
                    @close-edit="closeEdit"
                    @data-updated="dataUpdate"
                ></EditUser
            ></VDialog>
        </VCard>
    </IndexLayout>
</template>
<style scoped>
:deep(td.v-data-table__td.v-data-table-column--align-start:last-child) {
    width: 100px !important;
}
</style>
