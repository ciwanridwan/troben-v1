<template>
  <trawl-table
    :columns="manifestColumns"
    :dataSource="dataSource"
    :pagination="pagination"
    @changePage="changePage"
    @changeSizePage="changeSizePage"
  >
    <template v-if="record.driver" slot="driver" slot-scope="{ record }">
      <span  class="trawl-text-bold"> {{ record.driver.partners[0].code }}</span>
      <p>Tarif Rp. 2000 / Kg</p>
    </template>
    <template slot="manifest" slot-scope="{ record }">
      <a-space direction="vertical">
        <span v-if="record.code">
          {{ record.code.content }}
        </span>
        <span>
         <a-timeline :class="['trawl-timeline']">
        <a-timeline-item color="green">
          <template v-if="record.origin_partner">
            <span class="trawl-text-bold"> {{ record.origin_partner.address }}</span>
          </template>
        </a-timeline-item>
        <a-timeline-item color="green">
          <template v-if="record.partner">
            <span class="trawl-text-bold"> {{ record.partner.address }}</span>
          </template>
        </a-timeline-item>
      </a-timeline>
        </span>
        <span class="trawl-text-bold">Berat Aktual : {{ record.weight_borne_total }} Kg</span>
        <span> </span>
      </a-space>
    </template>

    <template slot="detail" slot-scope="{ record }">
      <span class="trawl-text-bolder">Full Customer Payment</span>
      <br/>
      <br/>
      <span>
        <li >
          Biaya Pengantaran
        </li>
      </span>
    </template>

    <template slot="balance" slot-scope="{ record }">
      <span class="trawl-text-bolder">{{ currency(record.packages.reduce((acc, curr) => acc + curr.total_amount, 0)) }}</span>
      <br/>
      <br/>
      <span>
          <li>
            {{ currency(record.packages.reduce((acc, curr) => acc + curr.transporter_funds, 0)) }}
          </li>
      </span>
    </template>

    <span slot="expandedRowRender" slot-scope="{ record }">
      <a-row type="flex" justify="space-between">
        <a-col :span="5">

        </a-col>
        <a-col :span="5">
            <span class="trawl-text-bold">Daftar Resi</span>
          <br/>
          <span>
            <li v-for="item in record.packages" :key="item.message">
              {{ item.code.content }}
            </li>
          </span>
        </a-col>
        <a-col :span="5">
            <span class="trawl-text-bold">Berat Aktual</span>
          <br/>
          <span>
            <li v-for="item in record.packages" :key="item.message">
              {{ item.total_weight }} Kg
            </li>
          </span>
        </a-col>
        <a-col :span="5">
          <br/>
          <span>
            <li v-for="item in record.packages" :key="item.message">
              {{ currency(item.transporter_funds) }}
            </li>
          </span>
        </a-col>
        <a-col :span="4">
          <br/>
          <span>
            <li v-for="item in record.packages" :key="item.message">
              {{ item.created_at }}
            </li>
          </span>
        </a-col>
      </a-row>
      <!--      <a-row type="flex" justify="space-between">-->
      <!--        <a-col :span="8">-->
      <!--          <delivery-status :record="record"></delivery-status>-->
      <!--        </a-col>-->
      <!--        <a-col :span="8">-->
      <!--          <admin-delivery-actions-->
      <!--            :delivery="record"-->
      <!--            @change="getDataFunction"-->
      <!--          />-->
      <!--        </a-col>-->
      <!--      </a-row>-->
    </span>

  </trawl-table>
</template>
<script>
import trawlTable from "../trawl-table.vue";
import manifestColumns from "../../config/table/payment-manifest";
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
  computed : {
  },
  mounted() {
    console.log(record.packages.reduce((acc, curr) => acc + curr.history_transporter['balance'], 0))
  },
  methods: {
    Sum(){
      console.log("sum")
      // return this.record.packages.reduce( (Sum, packages) => packages.total_amount + Sum  ,0);
    },
  },
  components: { trawlTable, DeliveryStatus, AdminDeliveryActions }
};
</script>
