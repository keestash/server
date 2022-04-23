<script>
export default {
  name: 'Modal',
  props: [
    'open'
  ],
  watch: {
    open: function (n, o) {
      if (n === o) return;
      const modal = new bootstrap.Modal(this.$refs['theModal']);
      if (!n) {
        modal.hide();
        return;
      }
      modal.show();
    }
  },
  created() {
    console.log('test');
  }
};
</script>

<template>
  <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
       ref="theModal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          ...
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save changes</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style>

</style>