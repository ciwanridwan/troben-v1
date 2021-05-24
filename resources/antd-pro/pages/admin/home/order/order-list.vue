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

            <!-- <template slot="overlay">
              <a-card class="">
                <a-checkbox-group
                  v-model="value"
                  name="checkboxgroup"
                  :options="plainOptions"
                  @change="onChange"
                />
              </a-card>
            </template> -->

            <!-- <a-menu slot="overlay">
              <a-menu-item>
                test
              </a-menu-item>
            </a-menu> -->
          </a-dropdown>
        </a-col>
      </a-row>
    </template>
    <template slot="content">
      <order-table :dataSource="items.data" />
      <!-- <a-table
        :columns="orderColumns"
        :data-source="items.data"
        :pagination="trawlbensPagination"
        @change="handleTableChanged"
      >
        <span slot="order_by" slot-scope="record">
          <a-badge
            v-if="record.order_by"
            :count="record.order_by"
            :class="['trawl-badge-success']"
          />
          <a-badge v-else :count="'Not Set'" :class="['trawl-badge-success']" />
        </span>

        <span slot="address" slot-scope="record">
          <a-timeline :class="['trawl-timeline']">
            <a-timeline-item color="green">
              <span>{{ record.sender_address }}</span>
            </a-timeline-item>
            <a-timeline-item color="green">
              <span>{{ record.receiver_address }}</span>
            </a-timeline-item>
          </a-timeline>
        </span>
        <span slot="expandedRowRender" slot-scope="record">
          <a-row type="flex" justify="space-between">
            <a-col :span="8">
              <order-status :record="record"></order-status>
            </a-col>
            <a-col :span="12" class="trawl-text-right" v-if="record.status">
              <order-action :afterAction="getItems" :record="record" />
            </a-col>
          </a-row>
        </span>
      </a-table> -->
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
