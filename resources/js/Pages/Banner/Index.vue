<script setup>
import { ref, watch } from "vue";
import { VueDraggableNext } from "vue-draggable-next";
import Swal from "sweetalert2";
import IndexLayout from "@/Layouts/IndexLayout.vue";
import AddBanner from "./AddBanner.vue";
import EditBanner from "./EditBanner.vue";
import SliderSort from "./SliderSort.vue";

const props = defineProps({
    banners: Object,
});

const selectedBanner = ref(null);
// const dialogView = ref(false);
const dialogEdit = ref(false);
const dialogAdd = ref(false);
const loading = ref(false);
const bannerList = ref(props.banners.data || []); // Initialize as array from props
const totalbanner = ref(props.banners.total || 0);
const model = ref([]);
const showDraggable1 = ref(false);
const showDraggable2 = ref(false);

// Watch the props.banners to update bannerList
watch(
    () => props.banners,
    (newBanners) => {
        bannerList.value = newBanners.data || [];
        totalbanner.value = newBanners.total || 0;
    },
    { immediate: true },
);

const loadItems = async () => {
    try {
        loading.value = true;
        const response = await axios.get(route("banner-management.fetch"));

        console.log(response.data.banners);

        featuredProdBanner.value = response.data.banners.featured_product;
        ourSponsorBanner.value = response.data.banners.our_sponsor;

        loading.value = false;
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: `Error fetching banners: ${error.message}`,
        });
        loading.value = false;
    }
};

const saveNewOrder = async (banner) => {
    try {
        const response = await axios.post(
            route("banner-management.updateOrder"),
            {
                banners: banner,
            },
        );
        const type = banner[0].group;
        Swal.fire({
            icon: "success",
            title: "Success",
            text: response.data.message,
        });
        toggleSort(type);
    } catch (error) {
        console.error("Error updating order:", error);
    }
};

const dataUpdate = () => {
    loadItems();
};

const featuredProdBanner = ref(props.banners.featured_product);
const ourSponsorBanner = ref(props.banners.our_sponsor);

const addGroup = ref("");

const addItem = (group) => {
    // Open the dialog
    addGroup.value = group;
    dialogAdd.value = true;
};

const editItem = (item) => {
    selectedBanner.value = item;
    // Open the dialog
    dialogEdit.value = true;
};

const closeAdd = () => {
    dialogAdd.value = false;
};

const closeEdit = () => {
    dialogEdit.value = false;
};

// const toggleSort = (type) => {
//   if (type === 'featured_product') {
//     showDraggable1.value = !showDraggable1.value;
//   } else {
//     showDraggable2.value = !showDraggable2.value;
//   }
// };

const deleteBanner = (banner, index) => {
    banner.splice(index, 1);
};
</script>

<template>
    <IndexLayout
        name="Banner Management"
        @add-item="addItem"
        :hideButton="true"
        icon="mdi-plus"
    >
        <div class="py-5 my-5">
            <SliderSort
                title="Featured Products"
                group="featured_product"
                :banners="featuredProdBanner"
                @add-button="addItem"
                @edit-button="editItem"
                @save-new-banners="saveNewOrder"
                @delete-banner="deleteBanner"
            />
        </div>
        <div class="py-5 my-5">
            <SliderSort
                title="Our Sponsors"
                group="our_sponsor"
                :banners="ourSponsorBanner"
                @add-button="addItem"
                @edit-button="editItem"
                @save-new-banners="saveNewOrder"
                @delete-banner="deleteBanner"
            />
        </div>
        <VDialog
            v-model="dialogAdd"
            max-width="800px"
            persistent
            z-index="1000"
        >
            <AddBanner
                :group="addGroup"
                @close-add="closeAdd"
                @data-updated="dataUpdate"
            ></AddBanner>
        </VDialog>
        <VDialog
            v-model="dialogEdit"
            max-width="800px"
            persistent
            z-index="1000"
        >
            <EditBanner
                :banner="selectedBanner"
                @close-edit="closeEdit"
                @data-updated="dataUpdate"
            ></EditBanner>
        </VDialog>
        <VDialog v-model="loading" max-width="800px" persistent z-index="1000">
            <div class="text-center">
                <VProgressCircular
                    indeterminate
                    color="primary"
                ></VProgressCircular>
            </div>
        </VDialog>
    </IndexLayout>
</template>
<style scoped>
:deep(td.v-data-table__td.v-data-table-column--align-start:last-child) {
    width: 100px !important;
}
</style>
