<template>
  <content-layout siderPosition="right">
    <template slot="content">
      <a-table
        :columns="orderColumns"
        :data-source="orders.data"
        :defaultExpandAllRows="true"
        :class="['trawl-table-expanded']"
      >
        <span slot="order_by" slot-scope="record">
          <a-badge :count="record.order_by" :class="['trawl-badge-success']" />
        </span>

        <span slot="address" slot-scope="record">
          <a-timeline :class="['trawl-timeline']">
            <a-timeline-item color="green">
              <span>{{ record.sender_address }}</span>
            </a-timeline-item>
            <a-timeline-item color="green">
              <span>{{ record.receiver_address }}</span>
            </a-timeline-item>
          </a-timeline>
        </span>
        <span slot="expandedRowRender" slot-scope="record">
          <a-row type="flex" justify="space-between">
            <a-col :span="8">
              <a-badge status="warning" text=""></a-badge>
              <span :class="['trawl-status-warning']"
                >Menunggu Konfirmasi Order</span
              >
            </a-col>
            <a-col :span="3">
              <a-button type="danger" ghost>Cancel</a-button>
            </a-col>
          </a-row>
        </span>
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
