<template>
  <trawl-table :columns="receiptColumns" :dataSource="dataSource">
    <template slot="address" slot-scope="{ record }">
      <a-timeline :class="['trawl-timeline']">
        <a-timeline-item color="green">
          <span>{{ record.sender_address }}</span>
        </a-timeline-item>
        <a-timeline-item color="green">
          <span>{{ record.receiver_address }}</span>
        </a-timeline-item>
      </a-timeline>
    </template>
    <span slot="order_by" slot-scope="{ record }">
      <a-badge
        v-if="record.order_by"
        :count="record.order_by"
        :class="['trawl-badge-success']"
      />
      <a-badge v-else :count="'Not Set'" :class="['trawl-badge-success']" />
    </span>
    <span slot="expandedRowRender" slot-scope="{ record }">
      <a-row type="flex" justify="space-between">
        <a-col :span="8">
          <order-status :record="record"></order-status>
        </a-col>
      </a-row>
    </span>
  </trawl-table>
</template>
<script>
import trawlTable from "../trawl-table.vue";
import receiptColumns from "../../config/table/home/trawl-receipt";
import OrderStatus from "../order-status.vue";
import OrderAction from "../order-action.vue";

export default {
  data() {
    return {
      receiptColumns
    };
  },
  props: {
    dataSource: Array,
    default: () => {
      return [];
    }
  },
  components: { trawlTable, OrderAction, OrderStatus }
};
</script>
