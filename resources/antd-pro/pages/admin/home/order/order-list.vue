<template>
  <content-layout
    siderPosition="right"
    :search="{ action: searchById, placeholder: 'cari id order ...' }"
  >
    <template slot="head-tools">
      <a-row type="flex" justify="end" :gutter="12">
        <a-col :span="8">
          <a-dropdown :trigger="['click']">
            <a class="ant-dropdown-link" @click="(e) => e.preventDefault()">
              Click me <a-icon type="down" />
            </a>
          </a-dropdown>
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
    <!-- <template slot="sider">
      <trawl-notification></trawl-notification>
    </template> -->
  </content-layout>
</template>
<script>
import ContentLayout from "../../../../layouts/content-layout.vue";
// import TrawlNotification from "../../../../components/trawl-notification.vue";
import OrderTable from "../../../../components/tables/order-table.vue";
export default {
  name: "MasterOrder",
  components: {
    ContentLayout,
    // TrawlNotification,
    OrderTable,
  },
  data: () => {
    return {
      recordNumber: 0,
      items: {},
      filter: {
        q: null,
        page: 1,
        per_page: 10,
      },
      loading: false,
      orderModalVisibility: false,
      orderModalObject: {},
      pagination: {},
    };
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
    },
  },

  mounted() {
    this.items = this.getDefaultPagination();
    this.getItems();
  },
};
</script>

<!-- <style lang="scss">
.order-notification-item {
  margin: 10px 0;
}
</style> -->
