<template>
  <content-layout
    siderPosition="right"
    :search="{ action: searchById, placeholder: 'cari id order ...' }"
  >
    <template slot="head-tools">
      <a-row type="flex" justify="end" :gutter="12">
        <a-col :span="8">
          <a-dropdown :trigger="['click']">
            <a class="ant-dropdown-link" @click="e => e.preventDefault()">
              Click me <a-icon type="down" />
            </a>
          </a-dropdown>
        </a-col>
      </a-row>
    </template>
    <template slot="content">
      <order-table
        :pagination="pagination"
        :dataSource="items.data"
        :get-data-function="getItems"
        :change-page="changePage"
      />
    </template>
    <template slot="sider">
      <trawl-notification></trawl-notification>
    </template>
  </content-layout>
</template>
<script>
import ContentLayout from "../../../../layouts/content-layout.vue";
import TrawlNotification from "../../../../components/trawl-notification.vue";
import OrderTable from "../../../../components/tables/order-table.vue";
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
        page: 1,
        per_page: 5
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
      this.pagination = {
        current_page: resp.current_page,
        first_page_url: resp.first_page_url,
        from: resp.from,
        last_page_url: resp.last_page_url,
        last_page: resp.last_page,
        next_page_url: resp.next_page_url,
        path: resp.path,
        per_page: Number(resp.per_page),
        prev_page_url: resp.prev_page_url,
        to: resp.to,
        total: resp.total
      };
    },
    afterAssign() {
      this.getItems();
    },
    searchById(value) {
      this.filter.q = value;
      this.getItems();
    },
    changePage(value) {
      this.filter.page = value;
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
