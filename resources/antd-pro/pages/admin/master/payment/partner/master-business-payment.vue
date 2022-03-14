<template>
  <content-layout title="Data Pendapatan Mitra">
    <template slot="head-tools">
      <a-row type="flex" justify="space-between" :gutter="[10, 10]">
        <a-col :span="8" style="text-align:center">
          <a-row
            type="flex"
            justify="space-between"
            :gutter="[10, 10]"
          >
            <a-col :span="10">
              <a-date-picker></a-date-picker>
            </a-col>
            <a-col :span="4">
              <span>s/d</span>
            </a-col>
            <a-col :span="10">
              <a-date-picker></a-date-picker>
            </a-col>
          </a-row>
        </a-col>
        <a-col :span="8">
          <a-input-search
            placeholder="Cari kode mitra"
            v-model="filter.partner_code"
            @search="getItems"
          ></a-input-search>
        </a-col>
        <a-col :span="8">
          <a-input-search
            placeholder="Cari kode resi"
            v-model="filter.q"
            @search="getItems"
          ></a-input-search>
        </a-col>
      </a-row>
    </template>
    <template slot="content">
      <a-card>
        <a-row
          type="flex"
          justify="space-between"
          align="middle"
          :gutter="[64, 10]"
        >
          <a-col :class="['trawl-border-right']" :span="12">
            <h3>Jumlah total resi</h3>
            <h2>
              <b>{{ items.total }} Order</b>
            </h2>
          </a-col>
          <a-col :span="12">
            <a-row type="flex" justify="space-between" align="middle">
              <a-col :span="12">
<!--                <h4>Jumlah total resi</h4>-->
<!--                <span><b>{{ items.total }}</b></span>-->
              </a-col>
<!--              <a-col :span="4">-->
<!--                <h4>Jml. Pendapatan:</h4>-->
<!--                <span-->
<!--                ><b>{{ currency(150) }}</b></span-->
<!--                >-->
<!--              </a-col>-->
<!--              <a-col :span="4">-->
<!--                <h4>Jml. Berat:</h4>-->
<!--                <span-->
<!--                ><b>{{ items.data.reduce((acc, item) => acc + item.total_weight, 0) }}</b></span>-->
<!--              </a-col>-->
            </a-row>
          </a-col>
        </a-row>
      </a-card>
      <order-table
        :dataSource="items.data"
        :get-data-function="getItems"
        :pagination="pagination"
        :change-page="changePage"
        :change-size-page="changeSizePage"
      />
    </template>
  </content-layout>
</template>
<script>
import ContentLayout from "../../../../../layouts/content-layout.vue";
import TrawlNotification from "../../../../../components/trawl-notification.vue";
import OrderTable from "../../../../../components/tables/business-payment-table";
export default {
  name: "MasterOrder",
  components: {
    ContentLayout,
    TrawlNotification,
    OrderTable
  },
  data: () => {
    return {
      recordNumber: 0,
      items: {},
      filter: {
        q: null,
        partner_code: null,
        page: 1,
        per_page: 10
      },
      loading: false,
      orderModalVisibility: false,
      orderModalObject: {},
      pagination: {}
    };
  },
  methods: {
    onSuccessResponse(resp) {
      this.items = resp;
      let numbering = this.items.from;
      _.forEach(this.items.data, o => {
        o.number = numbering++;
      });
      this.pagination = this.trawlbensPagination;
    },
    afterAssign() {
      this.getItems();
    },
    searchById(value) {
      this.filter.q = value;
      this.getItems();
    },
    changePage(currentPage) {
      this.filter.page = currentPage;
      this.getItems();
    },
    changeSizePage(sizePage) {
      this.filter.page = 1;
      this.filter.per_page = sizePage;
      this.getItems();
    }
  },

  mounted() {
    this.items = this.getDefaultPagination();
    this.getItems();
    console.log('asik', this.items.data);
  }
};
</script>

<style lang="scss">
.order-notification-item {
  margin: 10px 0;
}
</style>
