<template>
  <a-layout-content>
    <a-row type="flex" :gutter="20">
      <a-col :span="18" class="order-table">
        <a-table :dataSource="orders.data" :columns="columns"></a-table>
      </a-col>
      <a-col :span="6" class="order-notification">
        <a-card class="order-notification-title">
          <slot name="title">
            <h3>Notifikasi</h3>
          </slot>
        </a-card>
        <a-card
          class="order-notification-item"
          v-for="index in 10"
          :key="index"
        >
          test
        </a-card>
      </a-col>
    </a-row>
  </a-layout-content>
</template>
<script>
import { orders } from "../../../mock";

const columns = [
  {
    title: "No",
    dataIndex: "id"
  },
  {
    title: "ID Order",
    dataIndex: "barcode"
  },
  {
    title: "Mitra Penerima",
    dataIndex: "receiver_name"
  },
  {
    title: "Lokasi Pengiriman",
    dataIndex: "sender_address"
  },
  {
    title: "Order By",
    dataIndex: "order_by",
    sorter: true
  },
  {
    title: "Tanggal Order",
    dataIndex: "created_at"
  }
];

export default {
  data: () => {
    return {
      orders,
      columns
    };
  },
  created() {
    // console.log(this.orders);
    // console.log(this.orders);
  }
};
</script>

<style lang="scss">
.order-notification-item {
  margin: 10px 0;
}
</style>
