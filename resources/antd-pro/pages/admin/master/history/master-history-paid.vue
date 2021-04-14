<template>
  <content-layout>
    <template slot="head-tools">
      <a-row type="flex" justify="end">
        <a-col>
          <a-input-search
            v-model="filter.q"
            @search="getItems"
          ></a-input-search>
        </a-col>
      </a-row>
    </template>
    <template slot="content">
      <a-table
        :columns="historyPaidColumns"
        :data-source="items.data"
        :pagination="trawlbensPagination"
        @change="handleTableChanged"
        :loading="loading"
        :class="['trawl']"
      >
        <span slot="number" slot-scope="number">{{ number }}</span>
        <span
          slot="expandedRowRender"
          slot-scope="row"
          v-if="row.payment_status === 'pending'"
        >
          <a-row type="flex" justify="end">
            <a-col :span="12" :class="['trawl-text-right']">
              <modal-payment-verified
                :record="row"
                :paymentVerfied="paymentVerified"
              ></modal-payment-verified>
            </a-col>
          </a-row>
        </span>
        <!-- <span slot="action" slot-scope="record">
          <a-space>
            <delete-button @click="deleteConfirm(record)"></delete-button>
          </a-space>
        </span> -->
      </a-table>
    </template>
  </content-layout>
</template>
<script>
import contentLayout from "../../../../layouts/content-layout.vue";
import historyPaidColumns from "../../../../config/table/history";
import ModalPaymentVerified from "./modal-payment-verified.vue";

export default {
  components: { contentLayout, ModalPaymentVerified },
  data: () => ({
    recordNumber: 0,
    items: {},
    filter: {
      q: null,
      page: 1,
      per_page: 15
    },
    loading: false,
    historyPaidColumns
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
  created() {
    this.items = this.getDefaultPagination();
    this.getItems();
  }
};
</script>
