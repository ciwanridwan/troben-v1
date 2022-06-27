<template>
  <trawl-modal-confirm :ok="paymentConfirm">
    <template slot="trigger">
      <a-button class="trawl-button-success">Approve</a-button>
    </template>
    <template slot="text">
      <p>Apakah pembayaran telah berhasil dilunaskan?</p>
    </template>
  </trawl-modal-confirm>
</template>
<script>
import TrawlModalConfirm from "../trawl-modal-confirm.vue";
export default {
  props: {
    package: {
      type: Object,
      default: () => {},
    },
    afterConfirm: {
      type: Function,
      default: () => {},
    },
    routeSubmit: {
      type: String,
      default: "admin.payment.withdraw.request.confirmation",
    },
  },
  components: { TrawlModalConfirm },
  methods: {
    paymentConfirm() {
      this.$http
        .patch(
          this.routeUri(this.routeSubmit, {
            withdrawal_hash: this.record.hash
          })
        )
        .then(() => {
          this.afterConfirm();
          this.$emit("submit");
        });
    },
  },
};
</script>
