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
      <manifest-table :dataSource="items.data" />
    </template>
    <template slot="sider">
      <trawl-notification></trawl-notification>
    </template>
  </content-layout>
</template>
<script>
import ContentLayout from "../../../../layouts/content-layout.vue";
import TrawlNotification from "../../../../components/trawl-notification.vue";
import ManifestTable from "../../../../components/tables/manifest-table.vue";
export default {
  name: "MasterOrder",
  components: {
    ContentLayout,
    TrawlNotification,
    ManifestTable
  },
  data: () => {
    return {
      recordNumber: 0,
      items: {},
      filter: {
        q: null,
        page: 1,
        per_page: 15
      },
      loading: false,
      orderModalVisibility: false,
      orderModalObject: {}
    };
  },
  methods: {
    onSuccessResponse(resp) {
      this.items = resp;
      let numbering = this.items.from;
      _.forEach(this.items.data, o => {
        o.number = numbering++;
      });
    },
    afterAssign() {
      this.getItems();
    },
    searchById(value) {
      this.filter.q = value;
      console.log(value);
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
