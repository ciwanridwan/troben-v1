<template>
  <content-layout siderPosition="right">
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
              <order-status :record="record"></order-status>
            </a-col>
            <a-col :span="6" style="text-align:center">
              <order-modal
                :record="record"
                v-if="
                  record.package_status == 'pending' &&
                    record.payment_status == 'draft'
                "
                triggerText="Lihat"
              ></order-modal>
              <order-modal
                :record="record"
                v-else-if="
                  record.package_status != 'waiting_for_approval' &&
                    record.package_status != 'accepted'
                "
                :sendButton="true"
              ></order-modal>
              <order-modal-resi
                v-else-if="
                  record.package_status == 'accepted' &&
                    record.payment_status == 'paid'
                "
              ></order-modal-resi>
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
  data() {
    return {
      orderColumns,
      orders
    };
  }
};
</script>
