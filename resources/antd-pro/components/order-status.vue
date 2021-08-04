<template>
  <div>
    <div v-if="record.status == 'cancel'">
      <a-badge status="error" text=""></a-badge>
      <span :class="['trawl-status-error']">Pesan Dibatalkan</span>
    </div>
    <div v-else-if="record.status == 'created'">
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']">Menunggu Assign Mitra</span>
    </div>
    <div v-else-if="record.status == 'accepted' && record.payment_status == 'draft'">
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']">Menunggu Pembayaran Customer</span>
    </div>
    <div v-else-if="record.status == 'accepted' && record.payment_status == 'pending'">
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']">Menunggu konfirmasi admin</span>
    </div>
    <div v-else-if="record.status == 'waiting_for_pickup'">
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']">Menunggu Penjemputan</span>
    </div>
    <div v-else-if="record.status == 'picked_up'">
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']">Driver telah menerima barang</span>
    </div>
    <div v-else-if="record.status == 'estimating'">
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']">Sedang pengecekan di gudang</span>
    </div>
    <div v-else-if="record.status == 'estimated'">
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']">Selesai pengecekan di gudang</span>
    </div>
    <div v-else-if="record.status == 'waiting_for_packing'">
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']">Sedang menunggu packing</span>
    </div>
    <div v-else-if="record.status == 'packing'">
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']">Sedang dipacking</span>
    </div>
    <div v-else-if="record.status == 'packed'">
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']">Telah selesai packing</span>
    </div>
    <div v-else-if="record.status == 'waiting_for_approval'">
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']">Menunggu konfirmasi customer</span>
    </div>
    <div v-else-if="record.status == 'manifested'">
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']">Telah terassign di manifest</span>
    </div>
    <div v-else>
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']"
        >[Untracked Status] Please contact developer
      </span>
    </div>
  </div>
</template>
<script>
export default {
  props: ["record"],
};
</script>
