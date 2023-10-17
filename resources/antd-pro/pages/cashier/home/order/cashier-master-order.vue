<template>
  <content-layout
    siderPosition="right"
    :search="{ action: search, placeholder: 'cari id order ...' }"
  >
    <template slot="content">
      <order-table
        :dataSource="items.data"
        :get-data-function="getItems"
        :pagination="pagination"
        :change-page="changePage"
        :change-size-page="changeSizePage"
      />
    </template>
    <!-- <template slot="sider">
      <trawl-notification></trawl-notification>
    </template> -->
  </content-layout>
</template>
<script>
import contentLayout from "../../../../layouts/content-layout.vue";
import TrawlNotification from "../../../../components/trawl-notification";
import OrderTable from "../../../../components/tables/cashier/order-table.vue";

export default {
  components: {
    contentLayout,
    OrderTable,
    TrawlNotification,
  },
  methods: {
    onSuccessResponse(resp) {
      this.items = resp;
      let numbering = this.items.from;

      _.forEach(this.items.data, (o) => {
        o.number = numbering++;
      });
      this.pagination = this.trawlbensPagination;
    },
    async getParterInfo() {
      let { data } = await this.$http.get(this.routeUri(this.getRoute()), {
        params: {
          partner: true,
        },
      });
      this.partnerInfo = data.data;
    },
    changePage(currentPage) {
      this.filter.page = currentPage;
      this.getItems();
    },
    search(value) {
      this.filter.q = value;
      this.getItems();
    },
    changeSizePage(sizePage) {
      this.filter.page = 1;
      this.filter.per_page = sizePage;
      this.getItems();
    },
  },
  data() {
    return {
      items: this.getDefaultPagination(),
      partnerInfo: {},
      pagination: {},
    };
  },

  mounted() {
    this.getItems();
    this.getParterInfo();
  },
};
</script>
