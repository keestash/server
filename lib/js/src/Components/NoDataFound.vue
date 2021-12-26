<template>
    <div class="d-flex flex-column flex-grow-1 justify-content-center align-items-center ndf-wrapper share-no-data-found"
         v-if="this.visible"
    >
        <p class="h1 mb-2">
            <b-icon :icon="this.faClass" class="blue"></b-icon>
        </p>
        <h2 class="text-center">{{ this.header }}</h2>
        <p>{{ this.text }}</p>
    </div>
</template>

<script>
export default {
    name: "NoDataFound",
    props: [
        "visible",
        "header",
        "text",
        "type"
    ]
    , computed: {
        faClass: function () {
            if (this.type === 'user') {
                return 'person-circle';
            }
            if (this.type === 'attachment') {
                return 'fas fa-file fa-5x';
            }
            return 'fas fa-info fa-5x';
        }
    }
}
</script>

<style scoped>

</style>