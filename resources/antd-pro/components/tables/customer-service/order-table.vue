<template>
  <trawl-table :columns="orderColumns" :dataSource="dataSource">
    <span slot="expandedRowRender" slot-scope="{ record }">
      <a-row type="flex" justify="space-between" ref="expand">
        <a-col :span="8">
          <order-status :record="record"></order-status>
        </a-col>
        <a-col :span="6" style="text-align: center">
          <a-space>
            <modal-assign-transporter
              :delivery="record"
              @assigned="getDataFunction"
            ></modal-assign-transporter>
            <a-button type="danger" ghost>Tolak</a-button>
          </a-space>
        </a-col>
      </a-row>
    </span>
  </trawl-table>
</template>
<script>
import trawlTable from "../../trawl-table";
import orderColumns from "../../../config/table/customer-service/order";
import OrderStatus from "../../order-status";
import OrderAction from "../../order-action";
import OrderModal from "../../orders/order-modal";
import {
  getTotalWeightBorne,
  getTierPrice,
  getSubTotalItems,
} from "../../../functions/orders";
import ModalAssignTransporter from "../../modals/modal-assign-transporter.vue";
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
  components: {
    trawlTable,
    OrderAction,
    OrderStatus,
    OrderModal,
    ModalAssignTransporter,
  },
};
</script>
