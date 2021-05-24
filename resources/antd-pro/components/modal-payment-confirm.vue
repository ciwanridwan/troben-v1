<template>
  <trawl-modal-confirm :ok="paymentConfirm">
    <template slot="trigger">
      <a-button class="trawl-button-success">Lunas</a-button>
    </template>
    <template slot="text">
      <p>
        Apakah pembayaran telah berhasil dilunaskan?
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
    paymentConfirm() {
      this.$http
        .patch(
          this.routeUri("admin.home.paymentConfirm", {
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
