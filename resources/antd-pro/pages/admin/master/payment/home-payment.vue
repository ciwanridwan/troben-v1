<template>
  <content-layout title="Finance Dashboard">
    <template slot="content">
      <a-card style="height:50vh; width:74vw">
        <h2 style="text-align: center">Pendapatan Mitra {{ moment(filterChart.date).format('MMMM YYYY') }}</h2>
        <a-list :grid="{ gutter: 30, column: 2 }">
          <a-list-item style="padding: 8px">
            <div>
              <PlanetChart v-if="Object.keys(itemsChart).length" :itemsChart="itemsChart"/>
            </div>
          </a-list-item>
          <a-list-item style="padding: 55px">

            <a-row style="margin-bottom: 10px">
              <a-month-picker
                :size="size"
                valueFormat = 'YYYY-MM'
                v-model="filterChart.date"
                @change="getFinanceDataChart"/>
            </a-row>
            <a-row style="margin-bottom: 10px">
              <a-form-model-item prop="province_id">
                <a-select
                  placeholder="Pilih Provinsi"
                  show-search
                  @focus="getGeo('province')"
                  v-model="filterChart.province_id"
                  size="large"
                  :filter-option="filterOptionMethod"
                  :loading="loading"
                  @change="getFinanceDataChart"
                >
                  <a-select-option
                    v-for="(province, index) in provinces"
                    :key="index"
                    :value="province.id"
                  >
                    {{ province.name }}
                  </a-select-option>
                </a-select>
              </a-form-model-item>
            </a-row>
            <a-row >
              <a-form-model-item
                prop="regency_id"
              >
                <a-select
                  show-search
                  v-model="filterChart.regency_id"
                  size="large"
                  placeholder="Pilih Kota / Kabupaten"
                  defaultValue="Pilih Kota / Kabupaten"
                  :filter-option="filterOptionMethod"
                  @change="getFinanceDataChart"
                  @focus="
                  getGeo('regency', {
                    province_id: filterChart.province_id
                  })
                "
                  :loading="loading"
                >
                  <a-select-option
                    v-for="(regency, index) in regencies"
                    :key="index"
                    :value="regency.id"
                  >
                    {{ regency.name }}
                  </a-select-option>
                </a-select>
              </a-form-model-item>
            </a-row>
          </a-list-item>
        </a-list>
      </a-card>
      <template>
        <a-list :grid="{ gutter: 10, column: 2 }">
          <a-list-item style="padding: 8px">
            <a-card>
              <a-row
                :gutter="[30, 10]">
                <a-col :span="12">
                    <a-row type="flex">
                      <a-col>
                        <br/>
                        <h3>Pendapatan Mitra</h3>
                        <a-date-picker
                          :size="size"
                          valueFormat = 'YYYY-MM-DD'
                          v-model="filterDaily.date"
                          @change="getFinanceDataDaily"/>
                      </a-col>
                    </a-row>
                    <br/>
                    <a-row type="flex" style="vertical-align: middle">
                      <a-col>
                        <h2>{{  currency(itemsDaily.data.income_now).slice(0, -3) }}</h2>
                        <h3 :style="{'color': itemsDaily.data.income_difference < 0 ? 'red':'green'}">
                          <a-icon
                            :type="itemsDaily.data.income_difference < 0 ? 'down-circle': 'up-circle'"
                            theme="twoTone"
                            :two-tone-color="itemsDaily.data.income_difference < 0 ? '#fc0303':'#03fc24'"/> {{  currency(Math.abs(itemsDaily.data.income_difference)).slice(0, -3) }}</h3>
                      </a-col>
                    </a-row>
                    <br/>
                    <a-row type="flex" style="vertical-align: bottom" >
                      <a-col>
                        <h3>Pendapatan hari sebelumnya</h3>
                        <h3>{{  currency(itemsDaily.data.income_sub).slice(0, -3) }}</h3>
                      </a-col>
                    </a-row>
                </a-col>
                <a-col :span="12">
                  <a-row style="padding:5px">
                    <a v-bind:href="'/admin/payment/partner/space'">
                    <a-card>
                      <a-row :gutter="16">
                        <a-col :span="10">
                          <h3>MS</h3>
                          <h5>M.Space</h5>
                        </a-col>
                        <a-col :span="14">
                          <h4 align="middle" style="color: green; margin-top: 10px;">
                            {{  currency(itemsDaily.data.space.total_income).slice(0, -3) }}
                          </h4>
                        </a-col>
                      </a-row>
                    </a-card>
                    </a>
                  </a-row>
                  <a-row style="padding:5px">
                    <a v-bind:href="'/admin/payment/partner/business'">
                    <a-card>
                      <a-row :gutter="16">
                        <a-col :span="10">
                          <h3>MB</h3>
                          <h5>M.Business</h5>
                        </a-col>
                        <a-col :span="14">
                          <h4 align="middle" style="color: green; margin-top: 10px;">
                            {{  currency(itemsDaily.data.business.total_income).slice(0, -3) }}
                          </h4>
                        </a-col>
                      </a-row>
                    </a-card>
                    </a>
                  </a-row>
                  <a-row style="padding:5px">
                    <a v-bind:href="'/admin/payment/partner/pool'">
                      <a-card  style="cursor: pointer; ">
                        <a-row :gutter="16">
                          <a-col :span="10">
                            <h3 style="user-select: none">MPW</h3>
                            <h5 style="user-select: none">M.Warehouse</h5>
                          </a-col>
                          <a-col :span="14">
                            <h4 align="middle" style="color: green; margin-top: 10px; user-select: none">
                              {{  currency(itemsDaily.data.pool.total_income).slice(0, -3) }}
                            </h4>
                          </a-col>
                        </a-row>
                      </a-card>
                    </a>
                  </a-row>

                  <a-row style="padding:5px">
                    <a v-bind:href="'/admin/payment/partner/transporter'">
                    <a-card>
                      <a-row :gutter="16">
                        <a-col :span="10">
                          <h3>MT</h3>
                          <h5>M.Transpoter</h5>
                        </a-col>
                        <a-col :span="14">
                          <h4 align="middle" style="color: green; margin-top: 10px;">
                            {{  currency(itemsDaily.data.transporter.total_income , { precision: 1 }).slice(0, -3) }}
                          </h4>
                        </a-col>
                      </a-row>
                    </a-card>
                    </a>
                  </a-row>
                </a-col>
              </a-row>
            </a-card>
          </a-list-item>

          <a-list-item style="padding: 8px">
            <a-card>
              <a-row
                :gutter="[30, 10]">
                <a-col :span="12">
                  <a-row type="flex" style="vertical-align: top" >
                    <a-col>
                      <br/>
                      <h3>Pendapatan Mitra</h3>
                      <a-month-picker
                        :size="size"
                        :format="monthFormat"
                        valueFormat = 'YYYY-MM'
                        v-model="filterMonthly.date"
                        @change="getFinanceDataMonthly"
                      />
                    </a-col>
                  </a-row>
                  <br/>
                  <a-row type="flex" style="vertical-align: middle">
                    <a-col>
                      <h2>{{  currency(itemsMonthly.data.income_now).slice(0, -3) }}</h2>
                      <h3 :style="{'color': itemsMonthly.data.income_difference < 0 ? 'red':'green'}">
                        <a-icon
                          :type="itemsMonthly.data.income_difference < 0 ? 'down-circle': 'up-circle'"
                          theme="twoTone"
                          :two-tone-color="itemsMonthly.data.income_difference < 0 ? '#fc0303':'#03fc24'"/> {{  currency(Math.abs(itemsMonthly.data.income_difference)).slice(0, -3) }}</h3>
                    </a-col>
                  </a-row>
                  <br/>
                  <a-row type="flex" style="vertical-align: bottom" >
                    <a-col>
                      <h3>Pendapatan bulan sebelumnya</h3>
                      <h3>{{  currency(itemsMonthly.data.income_sub).slice(0, -3) }}</h3>
                    </a-col>
                  </a-row>
                </a-col>
                <a-col :span="12">
                  <a-row style="padding:5px">
                    <a v-bind:href="'/admin/payment/partner/space'">
                    <a-card>
                      <a-row :gutter="16">
                        <a-col :span="10">
                          <h3>MS</h3>
                          <h5>M.Space</h5>
                        </a-col>
                        <a-col :span="14">
                          <h4 align="middle" style="color: green; margin-top: 10px;">
                            {{  currency(itemsMonthly.data.space.total_income).slice(0, -3) }}
                          </h4>
                        </a-col>
                      </a-row>
                    </a-card>
                    </a>
                  </a-row>
                  <a-row style="padding:5px">
                    <a v-bind:href="'/admin/payment/partner/business'">
                    <a-card>
                      <a-row :gutter="16">
                        <a-col :span="10">
                          <h3>MB</h3>
                          <h5>M.Business</h5>
                        </a-col>
                        <a-col :span="14">
                          <h4 align="middle" style="color: green; margin-top: 10px;">
                            {{  currency(itemsMonthly.data.business.total_income).slice(0, -3) }}
                          </h4>
                        </a-col>
                      </a-row>
                    </a-card>
                    </a>
                  </a-row>
                  <a-row style="padding:5px">
                    <a v-bind:href="'/admin/payment/partner/pool'">
                    <a-card>
                      <a-row :gutter="16">
                        <a-col :span="10">
                          <h3>MPW</h3>
                          <h5>M.Warehouse</h5>
                        </a-col>
                        <a-col :span="14">
                          <h4 align="middle" style="color: green; margin-top: 10px;">
                            {{  currency(itemsMonthly.data.pool.total_income).slice(0, -3) }}
                          </h4>
                        </a-col>
                      </a-row>
                    </a-card>
                    </a>
                  </a-row>

                  <a-row style="padding:5px">
                    <a v-bind:href="'/admin/payment/partner/transporter'">
                    <a-card>
                      <a-row :gutter="16">
                        <a-col :span="10">
                          <h3>MT</h3>
                          <h5>M.Transpoter</h5>
                        </a-col>
                        <a-col :span="14">
                          <h4 align="middle" style="color: green; margin-top: 10px;">
                            {{  currency(itemsMonthly.data.transporter.total_income).slice(0, -3) }}
                          </h4>
                        </a-col>
                      </a-row>
                    </a-card>
                    </a>
                  </a-row>
                </a-col>
              </a-row>
            </a-card>
          </a-list-item>
        </a-list>
      </template>

    </template>
  </content-layout>
</template>

<style scoped>
.icons-list >>> .anticon {
  font-size: 100px;
}
</style>
<script>
import contentLayout from "../../../../layouts/content-layout.vue";
import paymentColumns from "../../../../config/table/payment";
import PlanetChart from '../../../../components/PlanetChart.vue';
export default {
  components: {
    contentLayout,
    PlanetChart
  },
  created() {
    this.items = this.getDefaultPagination();
    this.filterChart.date = this.today.toISOString().slice(0, 10);
    this.filterDaily.date = this.today.toISOString().slice(0, 10);
    this.filterMonthly.date = this.today.toISOString().slice(0, 10);
    this.getDataPartner();
  },
  data: () => ({
    recordNumber: 0,
    today  : new Date(),
    monthFormat: 'YYYY-MM',
    itemsDaily: {},
    itemsMonthly: {},
    itemsChart: {},
    filterDaily: {
      type: 'summary',
      summary_type : 'daily',
      date : null,
    },
    filterMonthly: {
      type: 'summary',
      summary_type : 'monthly',
      date : null,
    },
    filterChart: {
      type: null,
      date : null,
      province_id  : undefined,
      regency_id  : undefined,
    },
    loading: false,
    paymentColumns,
    provinces: [],
    regencies: [],
    form: {
      province_id: null,
      regency_id: null,
    },
    size: 'large',
    direction: 'vertical',
    type_business: 'business',
    type_space: 'space',
    type_transporter: 'transporter',
    type_pool: 'pool',
  }),
  methods: {
    onSuccessResponseDaily(response) {
      this.itemsDaily = response;
      let numbering = this.itemsDaily.from;
      this.itemsDaily.data.forEach((o, k) => {
        o.number = numbering++;
      });
    },
    onSuccessResponseMonthly(response) {
      this.itemsMonthly = response;
      let numbering = this.itemsMonthly.from;
      this.itemsMonthly.data.forEach((o, k) => {
        o.number = numbering++;
      });
    },
    onSuccessResponseChart(response) {
      this.itemsChart = response;
      let numbering = this.itemsChart.from;
      this.itemsChart.data.forEach((o, k) => {
        o.number = numbering++;
      });
    },
    searchById(value) {
      this.filter.q = value;
      this.getFinanceDataDaily();
    },
    navPartner() {
      this.$http
        .get(
          this.routeUri("admin.payment.partner"),
          {
            params: {
              type: this.type_pool
            }
          }
        )
    },
    filterOption(input, option) {
      return (
        option.componentOptions.children[0].text
          .toLowerCase()
          .indexOf(input.toLowerCase()) >= 0
      );
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
  },
  mounted() {
    this.getFinanceDataDaily();
    this.getFinanceDataMonthly();
    this.getFinanceDataChart();
  }
};
</script>

