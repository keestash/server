<template>
    <div>
        <b-modal
                class="modal-prevent-closing"
                :ref="this.refId"
                :title="this.modalTitle"
                @show="onOpen"
                @hidden="resetModal"
                @ok="onSubmit"
        >
            <p class="text-justify">
                {{this.description}}
            </p>
            <div class="text-center" v-if="loading">
                <b-spinner variant="primary" type="grow" label="Spinning"></b-spinner>
            </div>

            <form ref="form" @submit.stop.prevent="onSubmit" v-else-if="!loading && options.length > 0">
                <b-form-select v-model="selected" :options="options"
                               :select-size="4"></b-form-select>
            </form>
            <b-alert show variant="info" v-else :show="!loading && options.length === 0">
                {{ noDataText }}
            </b-alert>
        </b-modal>
    </div>
</template>

<script>
export default {
    name: "SelectableListModal",
    props: {
        refId: '',
        options: [],
        loading: true,
        noDataText: '',
        modalTitle: '',
        description: ''
    },
    data() {
        return {
            selected: null,
        }
    },
    created() {
        const _that = this;
        this.$parent.$on('onOpenModalClick', (refId) => {
            _that.$refs[refId].show();
        })
    },
    methods: {
        resetModal() {
            this.selected = null;
        },
        onSubmit() {
            this.$emit('onSubmit', this.selected);
        },
        onOpen() {
            this.$emit('onOpen');
        }
    }
}
</script>

<style scoped>

</style>