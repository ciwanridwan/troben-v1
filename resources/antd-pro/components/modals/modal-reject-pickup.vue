<template>
  <a-popconfirm @confirm="confirm" ok-text="Kunjungi Halaman Admin">
    <template #title>
      Untuk saat ini silahkan gunakan Admin versi Terbaru.
    </template>
    <a-button type="danger" ghost>
      <span>Tolak</span>
    </a-button>
  </a-popconfirm>
  <!-- <trawl-modal-confirm :ok="reject">
    <template slot="trigger">
      <a-button type="danger" ghost>Tolak</a-button>
    </template>
    <template slot="text"> Apakah kamu yakin ingin menolak pesanan? </template>
  </trawl-modal-confirm> -->
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
              "Pesanan " +
              this.delivery?.package?.code?.content +
              " berhasil ditolak",
          });
          this.$emit("reject");
        });
    },
    confirm() {
      window.open("https://admin.trawlbens.com/", "_blank");
    },
  },
};
</script>
<style>
.ant-btn.ant-btn-sm {
  display: none;
}
.ant-btn.ant-btn-primary.ant-btn-sm {
  display: block !important;
}
</style>
