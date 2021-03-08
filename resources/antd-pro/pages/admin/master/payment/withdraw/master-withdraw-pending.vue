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
      <a-checkbox-group v-model="selects" style="width:100%" @change="test">
        <a-table
          :columns="pendingColumns"
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
          <span slot="action" slot-scope="record">
            <a-space>
              <a-button type="primary" size="small">Selesai</a-button>
              <a-button type="danger" ghost size="small">Cancel</a-button>
            </a-space>
          </span>
          <span slot="selectAction" slot-scope="record">
            <a-checkbox :value="record"></a-checkbox>
          </span>
        </a-table>
      </a-checkbox-group>
    </template>
    <template slot="footer">
      <a-layout-footer
        v-show="selects.length > 0"
        :class="['trawl-content-footer']"
      >
        <a-row type="flex" justify="end" align="middle">
          <a-col :span="10">
            <a-space>
              <span>{{ selects.length }} Item terpilih</span>

              <a-button type="primary">Selesai</a-button>
              <a-button type="danger" ghost>Cancel</a-button>
            </a-space>
          </a-col>
        </a-row>
      </a-layout-footer>
    </template>
  </content-layout>
</template>
<script>
import pendingColumns from "../../../../../config/table/withdraw/pending";
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
    selects: [],
    pendingColumns,
    payments
  }),
  methods: {
    onSuccessResponse(response) {
      this.items = response;
      let numbering = this.items.from;
      this.items.data.forEach((o, k) => {
        o.number = numbering++;
      });
    },
    test(val) {}
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
