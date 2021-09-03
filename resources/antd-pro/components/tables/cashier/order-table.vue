<template>
  <trawl-table :columns="orderColumns"
               :dataSource="dataSource"
               :pagination="pagination"
               @changePage="changePage"
               @changeSizePage="changeSizePage">
    <span slot="expandedRowRender" slot-scope="{ record }">
      <a-row type="flex" justify="space-between" ref="expand">
        <a-col :span="8">
          <package-status-cashier :record="record" />
        </a-col>
        <a-col :span="6" style="text-align: center">
          <a-space>
            <cashier-order-actions :package="record" @change="getDataFunction" />
          </a-space>
        </a-col>
      </a-row>
    </span>
  </trawl-table>
</template>
<script>
import ModalAssignTransporter from "../../modals/modal-assign-transporter.vue";
import ModalRejectPickup from "../../modals/modal-reject-pickup.vue";
import packageStatus from "../../package-status-cashier.vue";
import orderColumns from "../../../config/table/cashier/order";
import OrderModal from "../../orders/modal/order-modal.vue";
import CashierOrderActions from "../../orders/actions/cashier-order-actions.vue";

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
    pagination: {
      type: Object,
      default: () => {}
    },
    changePage: {
      type: Function,
      default: () => {}
    },
    changeSizePage: {
      type: Function,
      default: () => {}
    }
  },
  components: {
    packageStatus,
    ModalAssignTransporter,
    ModalRejectPickup,
    OrderModal,
    CashierOrderActions,
  },
};
</script>
