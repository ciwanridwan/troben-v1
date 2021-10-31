<template>
  <div>
    <content-layout v-bind:title="type_partner + ' / ' +  moment(this.filterPartner.date).format('DD MMMM YYYY')">
      <template slot="head-tools">
       <a-row type="flex" justify="end" :gutter="10">
         <a-col>
           <a-date-picker
             valueFormat = 'YYYY-MM-DD'
             placeholder="Masukkan tanggal"
             v-model="filterPartner.date"
             @change="getDataPartner"/>
         </a-col>
         <a-col>
           <a-input-search
             v-model="filterPartner.q"
             placeholder="Masukkan kode mitra"
             @search="getDataPartner"
           ></a-input-search>
         </a-col>
       </a-row>
      </template>
      <template slot="content">
        <!-- table -->
        <a-table
          :columns="partnerColumns"
          :data-source="items.data"
          :pagination="trawlbensPagination"
          @change="handleTableChanged"
          :loading="loading"
          :class="['trawl']"
        >
          <span class="trawl-text-center" slot="partner_code" slot-scope="items">{{ items.partner_code }}</span>
          <span class="trawl-text-center" slot="partner_name" slot-scope="items">{{ items.partner_name }}</span>
          <span class="trawl-text-center" slot="partner_geo_regency" slot-scope="items">{{ items.partner_geo_regency }}</span>
          <span class="trawl-text-center" slot="balance" slot-scope="items">{{ currency(items.balance) }}</span>
        </a-table>
      </template>
    </content-layout>
  </div>
</template>

<script>
import partnerColumns from "../../../../../../config/table/payment-partner";
import ContentLayout from "../../../../../../layouts/content-layout.vue";
import moment from "moment";

export default {
  name: "customer-list",
  components: {
    ContentLayout
  },
  created() {
    this.items = this.getDefaultPagination();
    this.filterPartner.date = this.today;
    this.getDataPartner();
  },
  data: () => ({
    recordNumber: 0,
    items: {},
    today  : new Date(),
    filterPartner: {
      type : 'detail',
      partner_type  : 'transporter',
      date  : null,
      sortBy  : null,
      sort : null,
      q: null,
      page: null,
      per_page: -1
    },
    loading: false,
    size: 'large',
    type_partner: 'Mitra Transporter',
    partnerColumns
  }),
  methods: {
    onSuccessResponsePartner(response) {
      this.items = response;
      let numbering = this.items.from;
      this.items.data.forEach((o, k) => {
        o.number = numbering++;
      });
    },

    handleTableChanged(pagination) {
      this.filterPartner.page = pagination.current;
      this.filterPartner.per_page = pagination.pageSize;

      this.getDataPartner();
    }
  },
  mounted() {
    this.getDataPartner();
  },
};
</script>
