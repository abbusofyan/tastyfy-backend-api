<script setup>
import { ref, watch } from "vue";
import IndexLayout from "@/Layouts/IndexLayout.vue";
import Swal from "sweetalert2";
// import AddTopup from './AddTopup.vue';
// import EditTopup from './EditTopup.vue';

const props = defineProps({
    topups: Object,
});

const headers = [
    { title: "NAME", value: "name", sortable: true },
    { title: "AMOUNT", value: "amount", sortable: true },
    { title: "STATUS", value: "status", sortable: true },
    { title: "TRANSACTION DATE", value: "transaction_date", sortable: true },
    // { title: "ACTIONS", value: "actions", sortable: false },
];

const selectedTopup = ref(null);
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
const topupList = ref(props.topups.data || []); // Initialize as array from props
const totaltopup = ref(props.topups.total || 0);

// Watch the props.topups to update topupList
watch(
    () => props.topups,
    (newTopups) => {
        topupList.value = newTopups.data || [];
        totaltopup.value = newTopups.total || 0;
    },
    { immediate: true },
);

const loadItems = async ({ page, itemsPerPage, sortBy, search }) => {
    loading.value = true;

    search = searchVal.value;

    try {
        const response = await axios.get(route("topup-log.fetch"), {
            params: {
                page,
                itemsPerPage,
                sortBy: sortBy[0]?.key,
                sortOrder: sortBy[0]?.order,
                search,
            },
        });

        topupList.value = response.data.topups.data;
        totaltopup.value = response.data.topups.total;
        currentPage.value = response.data.topups.current_page;
    } catch (error) {
        console.error("Error fetching topups:", error);
    }

    loading.value = false;
};

const dataUpdate = () => {
    loadItems({
        page: currentPage.value,
        itemsPerPage: itemsPerPage.value,
        sortBy: [],
        search: search.value,
    });
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

const addItem = () => {
    // Open the dialog
    dialogAdd.value = true;
};

// const viewItem = (item) => {
//   selectedTopup.value = item;
//   // Open the dialog
//   dialogView.value = true;
// };

const exportCSV = async () => {
    try {
        const response = await axios.get(route("topup-log.export"));
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute(
            "download",
            `topup-log_${new Date().toLocaleDateString("en-GB").split("/").join("-")}.csv`,
        );
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (error) {
        Swal.fire({
            title: "Error!",
            text: error.response?.data?.message || "Failed to export data",
            icon: "error",
        });
    }
};

const editItem = (item) => {
    selectedTopup.value = item;
    // Open the dialog
    dialogEdit.value = true;
};

const closeAdd = () => {
    dialogAdd.value = false;
};

const closeEdit = () => {
    dialogEdit.value = false;
};
</script>

<template>
    <IndexLayout name="Topup Logs" :hide-button="true">
        <template #buttons-index>
            <VBtn
                color="success"
                variant="flat"
                prepend-icon="mdi-file-export"
                @click="exportCSV"
            >
                Export to CSV
            </VBtn>
        </template>
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
                :items="topupList"
                :items-length="totaltopup"
                :loading="loading"
                item-value="id"
                @update:options="loadItems"
                item-key="id"
                class="table-data borderless-table"
            >
                <template v-slot:item.name="{ item }">
                    {{ item.customer.user.name }} ({{
                        item.customer.user.email
                    }})
                </template>
                <template v-slot:item.amount="{ item }">
                    SGD {{ item.amount }}
                </template>
                <template v-slot:item.status="{ item }">
                    <VChip v-if="item.status === 0" tonal color="error"
                        ><strong>Failed</strong></VChip
                    >
                    <VChip v-if="item.status === 1" tonal color="success"
                        ><strong>Success</strong></VChip
                    >
                </template>
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
            <!-- <VDialog v-model="dialogAdd" max-width="800px" z-index="1000" persistent>
        <AddTopup @close-add="closeAdd" @data-updated="dataUpdate"></AddTopup
      ></VDialog>
      <VDialog v-model="dialogEdit" max-width="800px" z-index="1000" persistent>
        <EditTopup
          :topup="selectedTopup"
          @close-edit="closeEdit"
          @data-updated="dataUpdate"
        ></EditTopup
      ></VDialog> -->
        </VCard>
    </IndexLayout>
</template>
<style></style>
