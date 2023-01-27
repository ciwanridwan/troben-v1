<template>
  <content-layout title="Request Pencairan Mitra">
    <template slot="head-tools">
      <!-- <a-row type="flex" justify="end" :gutter="[10, 10]">
        <a-col :span="8">
          <a-input-search v-model="filter.q" @search="getItems"></a-input-search>
        </a-col>
      </a-row> -->
    </template>
    <template slot="content">
      <a-card>
        <a-row type="flex" justify="space-between" :gutter="[64, 10]">
          <a-col :class="['trawl-border-right']" :span="12">
            <h3>Jumlah Request</h3>
            <h2>
              <b>{{ items.total }}</b>
            </h2>
          </a-col>
          <!-- <a-col>
            <a-form-model-item prop="province_id">
              <a-select placeholder="Pilih Provinsi" show-search @focus="getGeo('province')"
                v-model="filterChart.province_id" size="large" :filter-option="filterOptionMethod" :loading="loading"
                @change="getFinanceDataChart">
                <a-select-option v-for="(province, index) in provinces" :key="index" :value="province.id">
                  {{ province.name }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </a-col> -->
          <a-col :class="['trawl-border-right']" :span="12">
            <h3>Total Request</h3>
            <h2>
              <b>{{ items.total }}</b>
            </h2>
          </a-col>
          <!--          <a-col :span="12">-->
          <!--            <a-row type="flex" justify="space-between" >-->
          <!--              <a-col :span="6">-->
          <!--                <h4>Saldo:</h4>-->
          <!--                <span-->
          <!--                ><b>{{ currency(150000) }}</b></span-->
          <!--                >-->
          <!--              </a-col>-->
          <!--              <a-col :span="6">-->
          <!--                <h4>Saldo:</h4>-->
          <!--                <span-->
          <!--                ><b>{{ currency(150000) }}</b></span-->
          <!--                >-->
          <!--              </a-col>-->
          <!--            </a-row>-->
          <!--          </a-col>-->
        </a-row>
      </a-card>
      <a-card>
        <a-row style="margin-bottom: 10px">
          <a-form-model-item prop="">
            <a-select placeholder="Pilih Provinsi" size="large">
              <a-select-option> test </a-select-option>
            </a-select>
          </a-form-model-item>
        </a-row>
      </a-card>
      <a-table
        :columns="requestColumns"
        :dataSource="items.data"
        :pagination="trawlbensPagination"
        @change="handleTableChanged"
        :loading="loading"
        :class="['trawl']"
        :row-selection="{
          onChange: onChangeSelected,
        }"
      >
        <span slot="number" slot-scope="number">{{ number }}</span>
        <span slot="action" slot-scope="record">
          <a-space v-if="record.status">
            <modal-reject-withdrawal
              :afterConfirm="afterAction"
              :record="record"
            />
            <modal-confirm-withdrawal
              :afterConfirm="afterAction"
              :record="record"
            />
          </a-space>
        </span>
      </a-table>
    </template>
    <template slot="footer">
      <a-layout-footer
        v-show="selections.length > 0"
        :class="['trawl-content-footer']"
      >
        <a-row type="flex" justify="end">
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
import requestColumns from "../../../../../config/table/withdraw/request";
import ContentLayout from "../../../../../layouts/content-layout.vue";
import AdminOrderActions from "../../../../../components/orders/actions/admin-order-actions.vue";
import ModalRejectWithdrawal from "../../../../../components/modal-finance/modal-reject-withdrawal";
import ModalConfirmWithdrawal from "../../../../../components/modal-finance/modal-confirm-withdrawal";

export default {
  components: {
    ContentLayout,
    AdminOrderActions,
    ModalRejectWithdrawal,
    ModalConfirmWithdrawal,
  },
  data: () => ({
    recordNumber: 0,
    items: {},
    filter: {
      q: null,
      page: 1,
      per_page: 15,
    },
    loading: false,
    requestColumns,
    selections: [],
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
  },
};
</script>
