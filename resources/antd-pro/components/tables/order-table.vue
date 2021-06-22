<template>
  <trawl-table :columns="orderColumns" :dataSource="dataSource">
    <template slot="id_order" slot-scope="{ record }">
      <order-modal :record="record">
        <span slot="trigger">
          <span class="trawl-text-bolder trawl-text-underline trawl-click">{{
            record.code.content
          }}</span>
        </span>
      </order-modal>
      <a-space direction="vertical" :size="1">
        <span
          >Total Charge Weight:
          <span class="trawl-text-bolder">{{ getTotalWeightBorne(record.items) }}</span>
          Kg
        </span>
        <span
          >Total per Kg:
          <span class="trawl-text-bolder">{{
            currency(getTierPrice(record.items))
          }}</span>
        </span>
        <span
          >Total Biaya:
          <span class="trawl-text-bolder">
            {{ currency(getSubTotalItems(record.items)) }}</span
          >
        </span>
      </a-space>
    </template>
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
        <a-col :span="12" class="trawl-text-right" v-if="record.status">
          <order-action :afterAction="getDataFunction" :record="record" />
        </a-col>
      </a-row>
    </span>
  </trawl-table>
</template>
<script>
import trawlTable from "../trawl-table.vue";
import orderColumns from "../../config/table/home/trawl-order";
import OrderStatus from "../../components/order-status.vue";
import OrderAction from "../../components/order-action.vue";
import OrderModal from "../../components/orders/modal/order-modal";
import {
  getTotalWeightBorne,
  getTierPrice,
  getSubTotalItems,
} from "../../functions/orders";
export default {
  data() {
    return {
      orderColumns,
    };
  },
  props: {
    dataSource: {
      type: Array,
      default: () => {
        return [];
      },
    },
    getDataFunction: {
      type: Function,
      default: () => {},
    },
  },
  methods: {
    getTotalWeightBorne,
    getTierPrice,
    getSubTotalItems,
  },
  components: { trawlTable, OrderAction, OrderStatus, OrderModal },
};
</script>
