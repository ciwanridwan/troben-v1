<template>
  <trawl-modal-confirm :ok="cancel">
    <template slot="trigger">
      <a-button type="danger" ghost>Cancel</a-button>
    </template>
    <template slot="text">
      <p>
        Apakah kamu ingin membatalkan pesanan?
      </p>
    </template>
  </trawl-modal-confirm>
</template>
<script>
import trawlModalConfirm from "./trawl-modal-confirm.vue";
export default {
  props: {
    record: {
      type: Object,
      default: () => {}
    },
    afterConfirm: {
      type: Function,
      default: () => {}
    }
  },
  components: { trawlModalConfirm },
  methods: {
    cancel() {
      this.$http
        .patch(
          this.routeUri("admin.home.cancel", {
            package_hash: this.record.hash
          })
        )
        .then(() => {
          this.afterConfirm();
        });
    }
  }
};
</script>
