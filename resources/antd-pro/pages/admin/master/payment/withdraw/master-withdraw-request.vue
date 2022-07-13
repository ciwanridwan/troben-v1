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
            <h3>Jumlah Request Mitra</h3>
            <h2>
              <b>{{ items.total }}</b>
            </h2>
          </a-col>
          <a-col>
            <h3>Total Request</h3>
            <h2>
              <b>{{ items.total }}</b>
            </h2>
          </a-col>
        </a-row>
      </a-card>
      <br>
      <a-card>
        <a-row type="flex" justify="space-between">
          <!-- <a-col :class="['trawl-border-right']" :span="6">
            <a-input-search v-model="filter.q" @search="getItems" placeholder="Cari kode mitra"></a-input-search>
          </a-col> -->

          <!-- Add Filter Partner Code -->
          <a-col :class="['trawl-border-right']" :span="4">
            <a-form-model-item prop="province_id">
              <a-select 
                placeholder="Pilih Mitra" 
                show-search @focus="getGeo('province')"
                v-model="filterChart.province_id" 
                size="large" 
                :filter-option="filterOptionMethod" 
                :loading="loading"
                @change="getFinanceDataChart"
              >
                <a-select-option v-for="(province, index) in provinces" :key="index" :value="province.id">
                  {{ province.name }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </a-col>
          <!-- End Filter Partner Code -->

          <!-- Add Filter Status -->
          <a-col :class="['trawl-border-right']" :span="4">
            <a-form-model-item prop="province_id">
              <a-select 
                placeholder="Filter Status" 
                show-search @focus="getGeo('province')"
                v-model="filterChart.province_id" 
                size="large" 
                :filter-option="filterOptionMethod" 
                :loading="loading"
                @change="getFinanceDataChart"
              >
                <a-select-option v-for="(province, index) in provinces" :key="index" :value="province.id">
                  {{ province.name }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </a-col>
          <!-- End Add Filter Status -->

          <!-- <a-col :class="['trawl-border-right']" :span="4">
            <a-input-search v-model="filter.q" @search="getItems" placeholder="Filter Status"></a-input-search>
          </a-col> -->
          <a-col :class="['trawl-border-right']" :span="2">
            <a-date-picker valueFormat='YYYY-MM-DD' placeholder="Start Date" />
          </a-col>
          <a-col>
            <h3>S/D</h3>
          </a-col>
          <a-col :class="['trawl-border-right']" :span="2">
            <a-date-picker valueFormat='YYYY-MM-DD' placeholder="End Date" />
          </a-col>
          <a-col :class="['trawl-border-right']" :span="2">
            <a-space>
              <!-- <modal-confirm-withdrawal :afterConfirm="afterAction" :record="record" /> -->
              <a-button type="success" class="trawl-button-success">Approve</a-button>
            </a-space>
          </a-col>
        </a-row>
      </a-card>
      <a-table :columns="requestColumns" :dataSource="items.data" :pagination="trawlbensPagination"
        @change="handleTableChanged" :loading="loading" :class="['trawl']">
        <span slot="number" slot-scope="number">{{ number }}</span>
        <span slot="action" slot-scope="record">
          <a-space v-if="record.status">

            <modal-reject-withdrawal :afterConfirm="afterAction" :record="record" />
            <modal-confirm-withdrawal :afterConfirm="afterAction" :record="record" />
          </a-space>
        </span>
      </a-table>
    </template>
    <template slot="footer">
      <!-- <a-layout-footer v-show="selections.length > 0" :class="['trawl-content-footer']"> -->
      <a-layout-footer :class="['trawl-content-footer']">
        <a-row type="flex" justify="end">
          <!-- <a-col :span="10">
            <a-space>
              <span>Item terpilih</span>

              <a-button type="primary" @click="showConfirm">Selesai</a-button>
              <a-button type="danger">Cancel</a-button>
            </a-space>
          </a-col> -->
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
      per_page: 15
    },
    loading: false,
    requestColumns,
    filterChart: {
      province_id: undefined,
    },
    loading: false,
    form: {
      province_id: null
    },
    // selections: [],
  }),
  methods: {
    onSuccessResponse(response) {
      this.items = response;

      let numbering = this.items.from;
      this.items.data.forEach((o, k) => {
        o.number = numbering++;
      });
    },

    async getGeo(status = "province", params = {}) {
      this.loading = true;
      params = { per_page: "-1", ...params };
      this.$http
        .get(this.routeUri("admin.payment.geo"), {
          params: {
            type: status,
            ...params
          }
        })
        .then(({ data }) => {
          let datas = data.data;
          this.putGeoToData(status, datas);
        })
        .finally(() => (this.loading = false));
    },
    putGeoToData(status, data) {
      switch (status) {
        case "province":
          this.provinces = data;
          break;
        case "regency":
          this.regencies = data;
          break;
      }
    },
    // onChangeSelected(selections) {
    //   this.selections = selections;
    // },
  },
  props: {
    record: {
      type: Object,
      default: () => { },
    },
    afterAction: {
      type: Function,
      default: () => { },
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
