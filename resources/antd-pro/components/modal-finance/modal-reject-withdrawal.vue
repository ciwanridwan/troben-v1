<template>
  <trawl-modal-confirm :ok="cancel">
    <template slot="trigger">
      <a-button type="danger" ghost>Request</a-button>
    </template>
    <template slot="text">
      <p>
        Apakah kamu ingin menolak pencairan dana?
      </p>
    </template>
  </trawl-modal-confirm>
</template>
<script>
import trawlModalConfirm from "../trawl-modal-confirm.vue";
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
          this.routeUri("admin.payment.withdraw.request.rejection", {
            withdrawal_hash: this.record.hash
          })
        )
        .then(() => {
          this.afterConfirm();
        });
    }
  }
};
</script>
