<template>
  <content-layout siderPosition="right">
    <template slot="content">
      <a-table
        :columns="orderColumns"
        :data-source="items.data"
        :defaultExpandAllRows="true"
        rowKey="barcode"
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
            <a-col :span="12" class="trawl-text-right">
              <a-space>
                <a-button type="danger" ghost>Cancel</a-button>
                <modal-assign-mitra
                  :order="record"
                  :visible="orderModalVisibility"
                  @ok="onOk"
                  @cancel="() => (orderModalVisibility = false)"
                />
              </a-space>
            </a-col>
          </a-row>
        </span>
      </a-table>
    </template>
    <template slot="sider">
      <trawl-notification></trawl-notification>
    </template>
  </content-layout>
</template>
<script>
import orderColumns from "../../../../config/table/home/trawl-order";
import ContentLayout from "../../../../layouts/content-layout.vue";
import OrderStatus from "./order-status.vue";
import TrawlNotification from "../../../../components/trawl-notification.vue";
import ModalAssignMitra from "./modal-assign-mitra.vue";
export default {
  name: "MasterOrder",
  components: {
    ContentLayout,
    OrderStatus,
    TrawlNotification,
    ModalAssignMitra
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
      orderColumns,
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
    assignOrder(order) {
      this.orderModalObject = order;
      this.orderModalVisibility = true;
    },
    onOk() {
      this.orderModalVisibility = false;

      this.getItems();
    }
  },
  created() {
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
