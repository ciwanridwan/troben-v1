<template>
  <content-layout
    siderPosition="right"
    :search="{ action: search, placeholder: 'cari id order ...' }"
  >
    <template slot="content">
      <order-table
        :data-source="items.data"
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
import TrawlNotification from "../../../../components/trawl-notification.vue";
import orderColumns from "../../../../config/table/customer-service/order";
import ContentLayout from "../../../../layouts/content-layout.vue";
import { orders } from "../../../../mock";
import { NoteIcon } from "../../../../components/icons";

import OrderModalResi from "../../../cashier/home/order/order-modal-resi.vue";
import orderModal from "../../../cashier/home/order/order-modal.vue";
import TrawlTable from "../../../../components/trawl-table.vue";
import OrderTable from "../../../../components/tables/customer-service/order-table-passed.vue";

export default {
  name: "CustomerServiceMasterOrder",
  components: {
    orderModal,
    OrderModalResi,
    TrawlNotification,
    ContentLayout,
    TrawlTable,
    OrderTable
  },
  data() {
    return {
      NoteIcon,
      orderColumns,
      orders,
      items: this.getDefaultPagination(),
      transporters: this.getDefaultPagination(),
      pagination: {}
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
            per_page: 10,
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
      this.pagination = this.trawlbensPagination;
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
    },
    search(value) {
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
    this.getItems();
  }
};
</script>
