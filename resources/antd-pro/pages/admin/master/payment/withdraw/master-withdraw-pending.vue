<template>
  <content-layout title="Data Pencairan Pending">
    <template slot="head-tools">
      <a-row type="flex" justify="space-between" :gutter="[10, 10]">
        <a-col :span="8">

        </a-col>
        <a-col :span="8" style="text-align:center">
          <a-row
            type="flex"
            justify="space-between"
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
        :columns="pendingColumns"
        :dataSource="items.data"
        :pagination="trawlbensPagination"
        @change="handleTableChanged"
        :loading="loading"
        :class="['trawl']"
        :row-selection="{
          onChange: onChangeSelected
        }"
      >
        <span slot="number" slot-scope="number">{{ number }}</span>

        <span slot="action" slot-scope="record">
          <a-space>
            <modal-cancel-withdrawal :afterConfirm="afterAction" :record="record" />
            <modal-done-withdrawal :afterConfirm="afterAction" :record="record" />
          </a-space>
        </span>
      </a-table>
    </template>
    <template slot="footer">
      <a-layout-footer
        v-show="selections.length > 0"
        :class="['trawl-content-footer']"
      >
        <a-row type="flex" justify="end" align="middle">
          <a-col :span="10">
            <a-space>
              <span>{{ selections.length }} Item terpilih</span>

              <a-button type="primary" @click="showConfirm">Selesai</a-button>
              <a-button type="danger">Cancel</a-button>
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
import ModalCancelWithdrawal from "../../../../../components/modal-finance/modal-cancel-withdrawal";
import ModalDoneWithdrawal from "../../../../../components/modal-finance/modal-done-withdrawal";
import AdminOrderActions from "../../../../../components/orders/actions/admin-order-actions";

export default {
  components: {
    ContentLayout,
    AdminOrderActions,
    ModalCancelWithdrawal,
    ModalDoneWithdrawal,
  },
  data: () => ({
    recordNumber: 0,
    items: {},
    filter: {
      q: null,
      page: 1,
      per_page: 15
    },
    loading: false,
    selections: [],
    pendingColumns,
  }),
  methods: {
    onSuccessResponse(response) {
      this.items = response;
      let numbering = this.items.from;
      this.items.data.forEach((o, k) => {
        o.number = numbering++;
      });
    },
    onChangeSelected(selections) {
      this.selections = selections;
    },
    showConfirm() {
      this.$confirm({
        title: 'Apakah anda yakin untuk mengkonfirmasi pencairan dana?',
        content: 'Some descriptions',
        onOk() {
          console.log('OK');
        },
        onCancel() {
          console.log('Cancel');
        },
        class: 'test',
      });
    },
    showRejectConfirm() {
      this.$confirm({
        title: 'Apakah anda yakin untuk menolak pencairan dana?',
        content: 'Some descriptions',
        onOk() {
          console.log('OK');
        },
        onCancel() {
          console.log('Cancel');
        },
        class: 'test',
      });
    }
  },
  props: {
    record: {
      type: Object,
      default: () => {},
    },
    afterAction: {
      type: Function,
      default: () => {},
    },
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
