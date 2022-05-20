<script>
import {Modal} from "bootstrap";

export default {
  name: 'Modal',
  props: {
    open: false,
    hasDescription: false,
    hasPositiveButton: {
      type: Boolean,
      default: true
    },
    closable: {
      type: Boolean,
      default: true
    },
    hasNegativeButton: {
      type: Boolean,
      default: true
    },
    uniqueId: ""
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
          '#' + this.uniqueId
          , {
            'backdrop': false,
            'keyboard': this.closable
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
    <div class="modal" tabindex="-1" role="dialog" :id="uniqueId" ref="central-modal-component-ref"
         v-bind:key="uniqueId">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body">
            <h4>
              <slot name="title"></slot>
            </h4>
            <slot name="body-description" v-if="hasDescription"></slot>
            <slot name="body"></slot>
            <div class="row justify-content-end">
              <div class="col flex-grow-0 p-0">
                <button type="button" class="btn btn-primary" @click="$emit('saved')" v-if="hasPositiveButton">
                  <slot name="button-text"></slot>
                </button>
              </div>
              <div class="col flex-grow-0">
                <button type="button" class="btn btn-secondary" @click="$emit('closed')" v-if="hasNegativeButton">
                  <slot name="negative-button-text"></slot>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>

</style>