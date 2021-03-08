<template>
  <content-layout>
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
      <a-card>
        <a-row
          type="flex"
          justify="space-between"
          align="middle"
          :gutter="[64, 10]"
        >
          <a-col :class="['trawl-border-right']" :span="12">
            <h3>Hari ini kamu mendapatkan</h3>
            <h2>
              <b>{{ payments.data.length }} Order</b>
            </h2>
          </a-col>
          <a-col :span="12">
            <a-row type="flex" justify="space-between" align="middle">
              <a-col :span="4">
                <h4>Jml Order</h4>
                <span><b>150</b></span>
              </a-col>
              <a-col :span="4">
                <h4>Jml Debit:</h4>
                <span
                  ><b>{{ currency(150) }}</b></span
                >
              </a-col>
              <a-col :span="4">
                <h4>Jml Kredit:</h4>
                <span
                  ><b>{{ currency(150) }}</b></span
                >
              </a-col>
            </a-row>
          </a-col>
        </a-row>
      </a-card>
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
import contentLayout from "../../../../layouts/content-layout.vue";
import paymentColumns from "../../../../config/table/payment";
import { payments } from "../../../../mock/index";

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
