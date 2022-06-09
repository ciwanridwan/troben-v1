<template>
  <trawl-table
    :columns="orderColumns"
    :dataSource="dataSource"
    :pagination="pagination"
    @changePage="changePage"
    @changeSizePage="changeSizePage"
  >
    <template slot="id_order" slot-scope="{ record }">
      <order-modal :package="record">
        <span slot="trigger">
          <span class="trawl-text-bolder trawl-text-underline trawl-click">{{
            record.code.content
          }}</span>
        </span>
      </order-modal>
      <package-table-detail :package="record" />
    </template>
<!--    <template slot="partner" slot-scope="{ record }">-->
<!--      <span>{{ record.deliveries[0].partner.name }}</span>-->
<!--      <br/>-->
<!--      <span>{{ record.deliveries[0].partner.code}}</span>-->
<!--    </template>-->
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
    <span slot="type" slot-scope="{ record }">
      <badge-package-type :package="record" />
    </span>
    <span slot="expandedRowRender" slot-scope="{ record }">
      <a-row type="flex" justify="space-between">
        <a-col :span="12">
          <order-status :record="record"></order-status>
        </a-col>
        <a-col :span="12" class="trawl-text-right" v-if="record.status">
          <admin-order-actions :package="record" @change="getDataFunction" />
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
  getSubTotalItems
} from "../../functions/orders";
import BadgePackageType from "../badges/badge-package-type.vue";
import AdminOrderActions from "../orders/actions/admin-order-actions.vue";
import PackageTableDetail from "../packages/package-table-detail.vue";
export default {
  data() {
    return {
      orderColumns
    };
  },
  props: {
    dataSource: {
      type: Array,
      default: () => {
        return [];
      }
    },
    getDataFunction: {
      type: Function,
      default: () => {}
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
  methods: {
    getTotalWeightBorne,
    getTierPrice,
    getSubTotalItems
  },
  components: {
    trawlTable,
    OrderAction,
    OrderStatus,
    OrderModal,
    BadgePackageType,
    AdminOrderActions,
    PackageTableDetail
  },
  mounted() {}
};
</script>
