<template>
  <content-layout title="Data Pendapatan Mitra">
    <template slot="head-tools">
      <a-row type="flex" justify="space-between" :gutter="[10, 10]">
        <a-col :span="8">
          <a-row
            type="flex"
            justify="space-between"
            align="middle"
            :gutter="[10, 10]"
          >
            <a-col :span="10">
              <a-date-picker></a-date-picker>
            </a-col>
            <a-col :span="4">
              <span>s/d</span>
            </a-col>
            <a-col :span="10">
              <a-date-picker></a-date-picker>
            </a-col>
          </a-row>
        </a-col>
        <a-col :span="8" style="text-align:center">
          <a-button icon="plus" type="success">
            Tambah Pendapatan
          </a-button>
        </a-col>
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
        :columns="paymentColumns"
        :data-source="payments.data"
        :pagination="trawlbensPagination"
        @change="handleTableChanged"
        :loading="loading"
        :class="['trawl']"
      >
        <span slot="number" slot-scope="number">{{ number }}</span>
        <span slot="debit" slot-scope="debit">{{ currency(debit) }}</span>
        <span slot="credit" slot-scope="credit">{{ currency(credit) }}</span>
        <span slot="action" slot-scope="record">
          <a-space>
            <delete-button @click="deleteConfirm(record)"></delete-button>
          </a-space>
        </span>
      </a-table>
    </template>
  </content-layout>
</template>
<script>
import contentLayout from "../../../../../layouts/content-layout";
import paymentColumns from "../../../../../config/table/payment";
import { payments } from "../../../../../mock/index";

export default {
  components: { contentLayout },
  data: () => ({
    recordNumber: 0,
    items: {},
    filter: {
      q: null,
      page: 1,
      per_page: 15
    },
    loading: false,
    paymentColumns,
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
