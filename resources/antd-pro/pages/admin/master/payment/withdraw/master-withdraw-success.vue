<template>
  <content-layout title="Data Pencairan">
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
      <a-card>
        <a-row
          type="flex"
          justify="space-between"
          align="middle"
          :gutter="[64, 10]"
        >
          <a-col :class="['trawl-border-right']" :span="12">
            <h3>Status Request</h3>
            <h2>
              <b>Berhasil &amp; Gagal</b>
            </h2>
          </a-col>
          <a-col :span="12">
            <a-row type="flex" justify="space-between" align="middle">
              <a-col :span="4">
                <h4>Jml. Pendapatan:</h4>
                <span
                ><b>{{ currency(150) }}</b></span
                >
              </a-col>
              <a-col :span="4">
                <h4>Jml. Berat:</h4>
                <span
                ><b>{{ currency(150) }}</b></span
                >
              </a-col>
            </a-row>
          </a-col>
        </a-row>
      </a-card>
      <a-table
        :columns="successColumns"
        :dataSource="items.data"
        :pagination="trawlbensPagination"
        @change="handleTableChanged"
        :loading="loading"
        :class="['trawl']"
      >
        <span slot="number" slot-scope="number">{{ number }}</span>
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
