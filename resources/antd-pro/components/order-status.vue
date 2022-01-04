<template>
  <div>
    <div v-if="record.status == 'cancel'">
      <a-badge status="error" text=""></a-badge>
      <span :class="['trawl-status-error']">Pesan Dibatalkan</span>
    </div>
    <div v-else-if="record.status == 'cancel_self_pickup'">
      <a-badge status="error" text=""></a-badge>
      <span :class="['trawl-status-error']">Pesan Dibatalkan dan paket akan diambil kembali oleh Customer</span>
    </div>
    <div v-else-if="record.status == 'cancel_delivered'">
      <a-badge status="error" text=""></a-badge>
      <span :class="['trawl-status-error']">Pesan Dibatalkan dan paket akan diantar kembali oleh Mitra</span>
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
    <div v-else-if="record.status == 'waiting_for_estimating'">
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']">Menunggu untuk dilakukan pengecekan di gudang</span>
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
      <span :class="['trawl-status-warning']">Telah terassign di manifest {{ record.deliveries[-1].code.content }}</span>
    </div>
    <div v-else-if="record.status == 'in_transit'">
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']">[transit] Barang sedang di proses mitra</span>
    </div>
    <div v-else-if="record.status == 'with_courier'">
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']">Barang sedang di antar kurir</span>
    </div>
    <div v-else-if="record.status == 'pending'">
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']">Mitra {{ record.deliveries[0].partner.code }} belum melakukan penerimaan pesanan</span>
    </div>
    <div v-else-if="record.status == 'delivered'">
      <a-badge status="success" text=""></a-badge>
      <span :class="['trawl-status-success']">Barang sudah diterima{{ record.received_by ? ' oleh ' + record.received_by : '.' }}</span>
    </div>
    <div v-else>
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']"
        >[Unhandled Status] Tolong report ke divisi IT bila menemui status ini
      </span>
    </div>
  </div>
</template>
<script>
export default {
  props: ["record"],
};
</script>
