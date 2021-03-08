<template>
  <content-layout siderPosition="right">
    <template slot="content">
      <a-table
        :columns="orderColumns"
        :data-source="orders.data"
        :defaultExpandAllRows="true"
        :class="['trawl-table-expanded']"
      >
        <span slot="expandedRowRender" slot-scope="record">{{ record }}</span>
        <span slot="expandIcon">test</span>
      </a-table>
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
import trawlTable from "../../../components/trawl-table.vue";
import ContentLayout from "../../../layouts/content-layout.vue";
import { orders } from "../../../mock";
import orderColumns from "../../../config/table/home/order";

export default {
  components: { trawlTable },
  data: () => {
    return {
      ContentLayout,
      orders,
      orderColumns
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
