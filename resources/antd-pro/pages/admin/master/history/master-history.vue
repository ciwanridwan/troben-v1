<template>
  <content-layout>
    <template slot="head-tools">
      <a-row type="flex" justify="end">
        <a-col>
          <!-- <a-input-search
            v-model="filter.q"
            @search="getItems"
          ></a-input-search> -->
        </a-col>
      </a-row>
    </template>
    <template slot="content">
      <!-- <order-table :dataSource="items.data" /> -->
    </template>
  </content-layout>
</template>
<script>
import contentLayout from "../../../../layouts/content-layout.vue";

import OrderTable from "../../../../components/tables/order-table.vue";

export default {
  components: {
    contentLayout,
    OrderTable
  },
  data: () => ({
    recordNumber: 0,
    items: {},
    filter: {
      q: null,
      page: 1,
      per_page: 15
    },
    loading: false
  }),
  methods: {
    onSuccessResponse(response) {
      this.items = response;
      let numbering = this.items.from;
      this.items.data.forEach((o, k) => {
        o.number = numbering++;
      });
    },
    async paymentVerified(package_hash) {
      let uri = this.routeUri("admin.history.paymentVerifed", {
        package_hash
      });
      await this.$http.patch(uri);
      this.getItems();
    }
  },
  mounted() {
    // this.items = this.getDefaultPagination();
    // this.getItems();
  }
};
</script>
