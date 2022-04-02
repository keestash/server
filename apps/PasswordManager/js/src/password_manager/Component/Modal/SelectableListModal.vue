<template>
  <div>
    <div class="modal" tabindex="-1" role="dialog" :id="this.idName">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ this.modalTitle }}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p class="text-justify">
              {{ this.description }}
            </p>
            <div class="text-center" v-if="loading">
              <div class="spinner-grow text-primary" role="status">
                <span class="sr-only"></span>
              </div>
            </div>

            <form ref="form" @submit.stop.prevent="onSubmit" v-else-if="!loading && options.length > 0">
              <div class="form-group">
                <select class="form-control">
                  <option v-model="selected" v-for="option in options" size="4">{{ option }}</option>
                </select>
              </div>

            </form>
            <div class="alert alert-info" role="alert" v-else
                 :class="(!loading && options.length === 0) ? 'show' : 'hide'">
              {{ noDataText }}
            </div>
            <div class="alert alert-info" role="alert" v-else
                 v-bind:class=" (!loading && options.length === 0) ? 'show' : 'hide'">
              {{ noDataText }}
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" @click="onSubmit">Save changes</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="resetModal">Close</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: "SelectableListModal",
  props: {
    idName: '',
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
    this.$parent.$on('onOpenModalClick', (refId) => {
      $("#" + refId).modal();
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