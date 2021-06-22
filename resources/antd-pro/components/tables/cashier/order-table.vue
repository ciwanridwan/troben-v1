<template>
  <trawl-table :columns="orderColumns" :dataSource="dataSource">
    <span slot="expandedRowRender" slot-scope="{ record }">
      <a-row type="flex" justify="space-between" ref="expand">
        <a-col :span="8">
          <package-status :record="record" />
        </a-col>
        <a-col :span="6" style="text-align: center">
          <a-space>
            <order-modal :record="record" @change="getDataFunction">
              <template slot="trigger">
                <a-button type="success" class="trawl-button-success"> Cek </a-button>
              </template>
            </order-modal>
          </a-space>
        </a-col>
      </a-row>
    </span>
  </trawl-table>
</template>
<script>
import ModalAssignTransporter from "../../modals/modal-assign-transporter.vue";
import ModalRejectPickup from "../../modals/modal-reject-pickup.vue";
import packageStatus from "../../package-status.vue";
import orderColumns from "../../../config/table/cashier/order";
import OrderModal from "../../orders/modal/order-modal.vue";

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
  components: {
    packageStatus,
    ModalAssignTransporter,
    ModalRejectPickup,
    OrderModal,
  },
};
</script>
