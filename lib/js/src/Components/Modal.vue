<script>
import {Modal} from "bootstrap";

export default {
  name: 'Modal',
  props: {
    open: false,
    hasDescription: false
  },
  data() {
    return {
      modal: null
    }
  },
  watch: {
    open: function (o, n) {
      const self = this;
      if (this.modal !== null) {
        this.modal.hide();
        this.modal = null;
        return;
      }

      this.modal = new Modal(
          '#central-modal-component'
          , {
            'backdrop': 'static'
          }
      );
      this.$refs["central-modal-component-ref"].addEventListener('hidden.bs.modal', function (e) {
        self.$emit('closed');
      })
      this.modal.show();
    }
  }
};
</script>

<template>
  <div>
    <div class="modal" tabindex="-1" role="dialog" id="central-modal-component" ref="central-modal-component-ref">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <slot name="title"></slot>
            <button type="button" class="close" aria-label="Close" @click="$emit('closed')">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <slot name="body-description" v-if="hasDescription"></slot>
            <slot name="body"></slot>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" @click="$emit('saved')">
              <slot name="button-text"></slot>
            </button>
            <button type="button" class="btn btn-secondary" @click="$emit('closed')">
              <slot name="negative-button-text"></slot>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>

</style>