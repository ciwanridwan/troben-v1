<template>
  <content-layout>
    <template slot="head-tools">
      <a-row type="flex" justify="end" :gutter="[10, 10]">
        <a-col :span="8">
          <a-input-search
            v-model="filter.q"
            @search="getItems"
          ></a-input-search>
        </a-col>
      </a-row>
    </template>
    <template slot="content">
      <a-table
        :columns="successColumns"
        :data-source="payments.data"
        :pagination="trawlbensPagination"
        @change="handleTableChanged"
        :loading="loading"
        :class="['trawl']"
      >
        <span slot="number" slot-scope="number">{{ number }}</span>
        <span slot="balance" slot-scope="balance">{{
          currency(10000000)
        }}</span>
        <span slot="withdraw_balance" slot-scope="withdraw_balance">{{
          currency(10000000)
        }}</span>
        <span slot="status" slot-scope="status">
          <span v-if="status" type="success" :class="['trawl-status-success']">
            Berhasil
          </span>
          <span v-else type="error" :class="['trawl-status-error']">
            Cancel
          </span>
        </span>
      </a-table>
    </template>
  </content-layout>
</template>
<script>
import successColumns from "../../../../../config/table/withdraw/success";
import ContentLayout from "../../../../../layouts/content-layout.vue";
import { payments } from "../../../../../mock/index";

export default {
  components: { ContentLayout },
  data: () => ({
    recordNumber: 0,
    items: {},
    filter: {
      q: null,
      page: 1,
      per_page: 15
    },
    loading: false,
    successColumns,
    payments
  }),
  methods: {
    onSuccessResponse(response) {
      this.items = response;
      let numbering = this.items.from;
      this.items.data.forEach((o, k) => {
        o.number = numbering++;
      });
    }
  },
  created() {
    this.items = this.getDefaultPagination();
    this.getItems();
    let numbering = this.payments.from;
    this.payments.data.forEach((o, k) => {
      o.number = numbering++;
    });
  }
};
</script>
