<template>
  <content-layout siderPosition="right">
    <template slot="content">
      <a-table
        :columns="orderColumns"
        :defaultExpandAllRows="true"
        :data-source="items.data"
      >
        <span slot="expandedRowRender" slot-scope="record">
          <a-row type="flex" justify="space-between" ref="expand">
            <a-col :span="8">
              <order-status :record="record"></order-status>
            </a-col>
            <a-col :span="6" style="text-align:center">
              <order-modal-resi
                v-if="record.payment_status == 'paid'"
              ></order-modal-resi>
              <order-modal
                :record="record"
                v-else
                :sendButton="true"
              ></order-modal>
            </a-col>
          </a-row>
        </span>
      </a-table>
    </template>
    <template slot="sider">
      <trawl-notification></trawl-notification>
    </template>
  </content-layout>
</template>
<script>
import { orders } from "../../../../mock";
import orderColumns from "../../../../config/table/cashier/order";
import contentLayout from "../../../../layouts/content-layout.vue";
import OrderModal from "./order-modal.vue";
import TrawlStatusWarning from "../../../../components/status/trawl-status-warning.vue";
import OrderStatus from "./order-status.vue";
import OrderModalResi from "./order-modal-resi.vue";
import { CalendarIcon } from "../../../../components/icons";
import TrawlNotification from "../../../../components/trawl-notification";

export default {
  components: {
    contentLayout,
    OrderModal,
    TrawlStatusWarning,
    OrderStatus,
    OrderModalResi,
    CalendarIcon,
    TrawlNotification
  },
  methods: {
    onSuccessResponse(resp) {
      this.items = resp;
      let numbering = this.items.from;

      _.forEach(this.items.data, o => {
        o.number = numbering++;
      });
    }
  },
  data() {
    return {
      orderColumns,
      orders,
      items: this.getDefaultPagination()
    };
  },

  mounted() {
    this.getItems();
  }
};
</script>
