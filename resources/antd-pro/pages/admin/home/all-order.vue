<template>
  <content-layout siderPosition="right">
    <template slot="content">
      <a-table
        :dataSource="orders.data"
        :columns="columns"
        :class="['trawl']"
      ></a-table>
    </template>
    <template slot="sider">
      <a-card class="order-notification-title">
        <slot name="title">
          <h3>Notifikasi</h3>
        </slot>
      </a-card>
      <a-card class="order-notification-item" v-for="index in 10" :key="index">
        test
      </a-card>
    </template>
  </content-layout>
</template>
<script>
import ContentLayout from "../../../layouts/content-layout.vue";
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
      ContentLayout,
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
