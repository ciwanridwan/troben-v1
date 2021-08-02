<template>
  <trawl-modal-confirm :ok="cancel">
    <template slot="trigger">
      <a-button type="danger" ghost>Cancel</a-button>
    </template>
    <template slot="text">
      <p>Apakah kamu ingin membatalkan pesanan?</p>
    </template>
  </trawl-modal-confirm>
</template>
<script>
import TrawlModalConfirm from "../trawl-modal-confirm.vue";
export default {
  props: {
    record: {
      type: Object,
      default: () => {},
    },
    afterConfirm: {
      type: Function,
      default: () => {},
    },
    package: {
      type: Object
    }
  },
  components: { TrawlModalConfirm },
  methods: {
    cancel() {
      this.$http
        .patch(
          this.routeUri("admin.home.cancel", {
            package_hash: this.package.hash,
          })
        )
        .then(() => {
          this.$emit("submit")
        });
    },
  },
};
</script>
