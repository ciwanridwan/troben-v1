<template>
  <content-layout>
    <template slot="content">
      <a-table
        :class="['trawl-table-expanded']"
        :columns="orderColumns"
        :defaultExpandAllRows="true"
        :data-source="orders.data"
      >
        <span slot="expandedRowRender" slot-scope="record">
          <a-row type="flex" justify="space-between" ref="expand">
            <a-col :span="8">
              <a-badge status="warning" text=""></a-badge>
              <span :class="['trawl-status-warning']"
                >Telah dihitung di Gudang(Oleh Ibrahim)</span
              >
            </a-col>
            <a-col :span="6" style="text-align:center">
              <order-modal :record="record"></order-modal>
            </a-col>
          </a-row>
        </span>
      </a-table>
    </template>
  </content-layout>
</template>
<script>
import { orders } from "../../../../mock";
import orderColumns from "../../../../config/table/cashier/order";
import contentLayout from "../../../../layouts/content-layout.vue";
import OrderModal from "./order-modal.vue";

export default {
  components: { contentLayout, OrderModal },
  data() {
    return {
      orderColumns,
      orders
    };
  }
};
</script>
