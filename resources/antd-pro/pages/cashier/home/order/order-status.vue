<template>
  <div>
    <trawl-status-warning v-if="record.package_status == 'counted'">
      Telah dihitung di Gudang (Oleh Ibrahim)
    </trawl-status-warning>
    <trawl-status-warning
      v-if="record.package_status == 'waiting_for_approval'"
    >
      Menunggu konfirmasi pelanggan
    </trawl-status-warning>
    <trawl-status-warning
      v-if="
        record.package_status == 'pending' && record.payment_status == 'draft'
      "
    >
      Revisi
    </trawl-status-warning>
    <trawl-status-warning
      v-if="
        record.package_status == 'accepted' &&
          record.payment_status == 'pending'
      "
    >
      Menunggu Pembayaran
    </trawl-status-warning>
    <trawl-status-warning
      v-if="
        record.package_status == 'waiting_for_payment' &&
          record.payment_status == 'pending'
      "
    >
      Menunggu Pembayaran
    </trawl-status-warning>
    <trawl-status-warning
      v-if="
        record.package_status == 'accepted' && record.payment_status == 'paid'
      "
    >
      Pembayaran berhasil
    </trawl-status-warning>
    <trawl-status-warning v-if="record.package_status == 'cancel'">
      Cancel
    </trawl-status-warning>
    <trawl-status-warning v-if="record.package_status == 'with_courier'">
      Diantar driver
    </trawl-status-warning>
  </div>
</template>
<script>
import trawlStatusWarning from "../../../../components/status/trawl-status-warning.vue";
export default {
  components: { trawlStatusWarning },
  props: ["record"]
};
</script>
