<template>
  <content-layout siderPosition="right">
    <template slot="content">
      <order-table :dataSource="items.data" :get-data-function="getItems" />
    </template>
    <template slot="sider">
      <trawl-notification></trawl-notification>
    </template>
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
    },
    async getParterInfo() {
      let { data } = await this.$http.get(this.routeUri(this.getRoute()), {
        params: {
          partner: true,
        },
      });
      this.partnerInfo = data.data;
    },
  },
  data() {
    return {
      items: this.getDefaultPagination(),
      partnerInfo: {},
    };
  },

  mounted() {
    this.getItems();
    this.getParterInfo();
  },
};
</script>
