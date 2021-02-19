<template>
  <a-layout-content>
    <a-row type="flex" :gutter="20">
      <a-col :span="18" class="order-table">
        <trawl-table :dataSource="orders.data" :columns="columns"></trawl-table>
      </a-col>
      <a-col :span="6" class="order-notification">
        <trawl-card class="order-notification-title">
          <slot name="title">
            <h3>Notifikasi</h3>
          </slot>
        </trawl-card>
        <trawl-card
          class="order-notification-item"
          v-for="index in 10"
          :key="index"
        >
          test
        </trawl-card>
      </a-col>
    </a-row>
  </a-layout-content>
</template>
<script>
import TrawlCard from "../../../components/trawl-card.vue";
import TrawlTable from "../../../components/trawl-table.vue";
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
  components: {
    TrawlTable,
    TrawlCard
  },
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
