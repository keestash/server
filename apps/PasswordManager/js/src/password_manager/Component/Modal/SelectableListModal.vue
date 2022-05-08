<template>
  <div>
    <div class="modal" tabindex="-1" role="dialog" id="selectable-list-modal">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ modalTitle }}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p class="text-center">
              {{ description }}
            </p>
            <div class="text-center" v-if="loading">
              <div class="spinner-grow text-primary" role="status">
                <span class="sr-only"></span>
              </div>
            </div>
            <div class="form-group" v-else-if="!loading && options.length > 0">
              <select class="form-control" v-model="selectedOption">
                <option v-for="option in options" size="4" :value="option.value" v-bind:key="option.value">
                  {{ option.text }}
                </option>
              </select>
            </div>
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
import {Modal} from "bootstrap";

export default {
  name: "SelectableListModal",
  props: {
    idName: '',
    options: Array,
    loading: {
      type: Boolean,
      default: true
    },
    noDataText: '',
    modalTitle: '',
    description: ''
  },
  data() {
    return {
      selectedOption: null,
    }
  },
  computed: {
    isLoading() {
      return this.loading === true;
    }
  },
  methods: {
    showModal() {
      const modal = new Modal("#selectable-list-modal");
      modal.show();
      this.onOpen();
    },
    resetModal() {
      this.selectedOption = null;
    },
    onSubmit: function (e) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      this.$emit('onSubmit', this.selectedOption);
    },
    onOpen() {
      this.$emit('onOpen');
    }
  }
}
</script>

<style scoped>

</style>