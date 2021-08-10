<template>
  <trawl-table
    :columns="manifestColumns"
    :dataSource="dataSource"
    :pagination="pagination"
    @changePage="changePage"
    @changeSizePage="changeSizePage"
  >
    <template slot="manifest" slot-scope="{ record }">
      <a-space direction="vertical">
        <span v-if="record.code">
          {{ record.code.content }}
        </span>
        <span
          >Jumlah Kg :
          <span class="trawl-text-bold"
            >{{ record.weight_borne_total }} Kg</span
          ></span
        >
        <span> </span>
      </a-space>
    </template>
    <template slot="origin_partner" slot-scope="{ record }">
      <template v-if="record.origin_partner">
        <span class="trawl-text-bold"> {{ record.origin_partner.code }}</span>
        <p>{{ record.origin_partner.address }}</p>
      </template>
    </template>
    <template slot="partner" slot-scope="{ record }">
      <template v-if="record.partner">
        <span class="trawl-text-bold"> {{ record.partner.code }}</span>
        <p>{{ record.partner.address }}</p>
      </template>
    </template>
    <span slot="expandedRowRender" slot-scope="{ record }">
      <a-row type="flex" justify="space-between">
        <a-col :span="8">
          <delivery-status :record="record"></delivery-status>
        </a-col>
        <a-col :span="8">
          <admin-delivery-actions
            :delivery="record"
            @change="getDataFunction"
          />
        </a-col>
      </a-row>
    </span>
  </trawl-table>
</template>
<script>
import trawlTable from "../trawl-table.vue";
import manifestColumns from "../../config/table/home/trawl-manifest";
import DeliveryStatus from "../delivery-status.vue";
import AdminDeliveryActions from "../orders/actions/admin-delivery-actions.vue";

export default {
  data() {
    return {
      manifestColumns
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
  components: { trawlTable, DeliveryStatus, AdminDeliveryActions }
};
</script>
