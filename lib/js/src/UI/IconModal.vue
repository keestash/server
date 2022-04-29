<script>
export default {
  name: 'IconModal',
  props: {
    id: {
      required: true,
      type: String
    }
  },
  methods: {
    close() {
      this.$emit('close');
    },
  },
};
</script>

<template>
  <div class="modal fade" v-bind:id="id" tabindex="-1" role="dialog" aria-labelledby="Vorschau">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Schließen"><span aria-hidden="true">&times;</span>
          </button>
          <slot name="subject"><h4 class="modal-title" id="Vorschau">Titel</h4></slot>
        </div>
        <div class="modal-body content">
          <slot name="body">Inhalt der Nachricht</slot>
        </div>
        <div class="modal-footer">
          <slot name="additional_btn"></slot>
          <slot name="close_btn">
            <button type="button" class="btn btn-default" data-dismiss="modal">Schließen</button>
          </slot>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.content {
  white-space: pre-wrap;
}
</style>