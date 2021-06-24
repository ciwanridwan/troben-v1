<template>
  <trawl-modal-confirm :ok="paymentConfirm">
    <template slot="trigger">
      <a-button class="trawl-button-success">Lunas</a-button>
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
      default: "admin.home.paymentConfirm",
    },
  },
  components: { TrawlModalConfirm },
  methods: {
    paymentConfirm() {
      this.$http
        .patch(
          this.routeUri(this.routeSubmit, {
            package_hash: this.package.hash,
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
