<template>
  <trawl-modal-confirm :ok="reject">
    <template slot="trigger">
      <a-button type="danger" ghost>Tolak</a-button>
    </template>
    <template slot="text"> Apakah kamu yakin ingin menolak pesanan? </template>
  </trawl-modal-confirm>
</template>
<script>
import TrawlModalConfirm from "../trawl-modal-confirm.vue";

export default {
  components: {
    TrawlModalConfirm,
  },
  props: {
    delivery: {
      type: Object,
      default: () => {},
    },
  },
  methods: {
    reject() {
      this.$http
        .patch(
          this.routeUri("partner.customer_service.home.order.reject", {
            delivery_hash: this.delivery?.hash,
          })
        )
        .then(() => {
          this.$notification.success({
            message:
              "Pesanan " + this.delivery?.package?.code?.content + " berhasil ditolak",
          });
          this.$emit("reject");
        });
    },
  },
};
</script>
