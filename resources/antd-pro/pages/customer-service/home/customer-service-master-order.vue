<template>
  <content-layout siderPosition="right">
    <template slot="content">
      <a-table
        :columns="orderColumns"
        :defaultExpandAllRows="true"
        :data-source="items.data"
      >
        <span slot="expandedRowRender" slot-scope="record">
          <a-row type="flex" justify="space-between" ref="expand">
            <a-col :span="8">
              <order-status :record="record"></order-status>
            </a-col>
            <a-col :span="6" style="text-align:center">
              <a-space>
                <modal-assign-transporter
                  :order="record"
                  :items="transporters"
                  :getTransporters="getTransporters"
                  :save="save"
                ></modal-assign-transporter>
                <a-button type="danger" ghost>Tolak</a-button>
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
import ModalAssignTransporter from "./modal-assign-transporter.vue";
import TrawlNotification from "../../../components/trawl-notification.vue";
import orderColumns from "../../../config/table/customer-service/order";
import ContentLayout from "../../../layouts/content-layout.vue";
import { orders } from "../../../mock";

import OrderModalResi from "../../cashier/home/order/order-modal-resi.vue";
import orderModal from "../../cashier/home/order/order-modal.vue";

export default {
  name: "CustomerServiceMasterOrder",
  components: {
    orderModal,
    OrderModalResi,
    TrawlNotification,
    ContentLayout,
    ModalAssignTransporter
  },
  data() {
    return {
      orderColumns,
      orders,
      items: this.getDefaultPagination(),
      transporters: this.getDefaultPagination()
    };
  },
  methods: {
    getTransporters: _.debounce(function(search = null, type = null) {
      this.loading = true;
      this.$http
        .get(this.routeUri(this.getRoute()), {
          params: {
            transporter: true,
            type: type,
            per_page: 2,
            q: search
          }
        })
        .then(({ data: responseData }) => {
          this.transporters = responseData;
        })
        .finally(() => (this.loading = false));
    }),
    onSuccessResponse(resp) {
      this.items = resp;
      let numbering = this.items.from;

      _.forEach(this.items.data, o => {
        o.number = numbering++;
      });
    },
    async save(data = {}) {
      this.loading = true;

      const response = await this.$http
        .patch(this.routeUri(this.getRoute() + ".assign", data))
        .then(resp => {
          this.getItems();
          this.$notification.success({
            message: "Sukses menugaskan transporter!"
          });
        })
        .finally(e => {
          this.onErrorResponse(e);
          this.loading = false;
        });

      return response;
    }
  },
  mounted() {
    this.getItems();
  }
};
</script>
