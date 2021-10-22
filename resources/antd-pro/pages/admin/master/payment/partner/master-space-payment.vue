<template>
  <content-layout title="Data Pendapatan Mitra">
    <template slot="head-tools">
      <a-row type="flex" justify="space-between" :gutter="[10, 10]">
        <a-col :span="8" >
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
import OrderTable from "../../../../../components/tables/space-payment-table";
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
  }
};
</script>

<style lang="scss">
.order-notification-item {
  margin: 10px 0;
}
</style>
