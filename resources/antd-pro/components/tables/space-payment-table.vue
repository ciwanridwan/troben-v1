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
      <br/>
      <span>
        <a-timeline :class="['trawl-timeline']">
        <a-timeline-item color="green">
          <span>{{ record.sender_address }}</span>
        </a-timeline-item>
        <a-timeline-item color="green">
          <span>{{ record.receiver_address }}</span>
        </a-timeline-item>
      </a-timeline>
      </span>
    </template>

    <template slot="detail" slot-scope="{ record }">
      <span class="trawl-text-bolder">Full Customer Payment</span>
      <br/>
      <br/>
      <span>
        <li v-for="item in record.history_space" :key="item.message">
          {{ item.description.charAt(0).toUpperCase() + item.description.substring(1) }}
        </li>
      </span>
    </template>

    <template slot="balance" slot-scope="{ record }">
      <span class="trawl-text-bolder trawl-text-center">{{ currency(record.total_amount) }}</span>
      <br/>
      <br/>
      <span>
          <li v-for="item in record.history_space" :key="item.message">
            {{ currency(item.balance) }}
          </li>
      </span>
    </template>
    <template slot="created_at" slot-scope="{ record }">
      <span class="trawl-text-center trawl-text-bolder">{{ moment(record.created_at).format("ddd, DD MMM YYYY HH:mm:ss") }}</span>
      <br/>
      <br/>
      <span>
          <li v-for="item in record.history_space" :key="item.message">
            {{ moment(item.created_at).format("ddd, DD MMM YYYY HH:mm:ss") }}
          </li>
      </span>
    </template>
    <span slot="expandedRowRender" slot-scope="{ record }">
      <a-row type="flex" justify="space-between">
        <a-col :span="8">
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
import orderColumns from "../../config/table/payment-space";
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
    },
    props: {
      package: {
        type: Object,
        default: () => {},
      },
    },
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
  computed: {
    total_weight() {
      return this.package?.total_weight;
    },
    tier_price() {
      return this.package?.tier_price;
    },
    total_amount() {
      return this.package?.total_amount;
    },
  },
  mounted() {}
};
//capitalize only the first letter of the string.
function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}
//capitalize all words of a string.
function capitalizeWords(string) {
  return string.replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
};
</script>
