<template>
  <content-layout title="Request Pencairan Mitra">
    <template slot="content">
      <a-card>
        <a-row type="flex" justify="space-between" :gutter="[64, 10]">
          <a-col :span="12">
            <h3 class="text-gray">Jumlah Request Mitra</h3>
            <h2 class="mb-0 title-price">
              <template v-if="request_disbursment.count == null || request_disbursment.count == 0">
                <b>0</b>
              </template>
              <template v-else>
               <b>Rp. {{ formatPrice(request_disbursment.count) }}</b>
              </template>
            </h2>
          </a-col>
          <a-col>
            <h3 class="text-gray">Total Request</h3>
            <h2 class="mb-0 title-price">
              <template v-if="total_request.amount == null || total_request.amount == 0">
                <b>0</b>
              </template>
              <template v-else>
                <b>Rp. {{ formatPrice(total_request.amount) }}</b>
              </template>
            </h2>
          </a-col>
        </a-row>
      </a-card>
      <br>
      <a-card>
        <a-row :gutter="[24]">
          <a-col :span="5">
            <a-form-model-item>
              <a-select 
                placeholder="Cari Kode Mitra"
                v-model="filter.code" 
                size="large" 
                :loading="loading"
                show-search
                :show-arrow="false"
                :filter-option="filterOptionMethod"
                :not-found-content="null"
              >
                <a-select-option :value="null">
                  Cari Kode Mitra
                </a-select-option>
                <a-select-option v-for="(code, index) in codes" :key="index" :value="code.code">
                  {{ code.code }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </a-col>
          <a-col :span="3"></a-col>
          <a-col :span="4">
            <a-form-model-item>
              <a-select 
                placeholder="Filter status" 
                v-model="filter_status" 
                size="large" 
                :loading="loading"
              >
                <a-select-option :value="null">
                  Filter status
                </a-select-option>
                <a-select-option value="approved">
                  Approved
                </a-select-option>
                <a-select-option value="requested">
                  Request
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </a-col>
          <a-col :span="4">
            <a-date-picker
              size="large"
              v-model="filter.start_date"
              placeholder="Start Date"
              valueFormat = 'YYYY-MM-DD'/>
          </a-col>
          <a-col :span="1">
            <h3 class="mb-0 text-center mt-date">s/d</h3>
          </a-col>
          <a-col :span="4">
            <a-date-picker
              size="large"
              v-model="filter.end_date"
              :disabled="filter.start_date == null"
              valueFormat='YYYY-MM-DD'
              @change="filterDate()"
              placeholder="End Date" />
          </a-col>
          <a-col :span="2">
            <a-space>
              <a-button type="success" class="trawl-button-success h-button" :disabled="filter.start_date == null || filter.end_date == null" @click="exportData()">
                <a-icon type="download"></a-icon>
                Export
              </a-button>
            </a-space>
          </a-col>
        </a-row>
      </a-card>

      <a-table
        :columns="requestColumns"
        :dataSource="filteredItems"
        :loading="loading"
        :class="['trawl']"
        rowKey="id"
      >
        <span slot="number" slot-scope="number" class="fw-bold">{{ number }}.</span>
        <span slot="code" slot-scope="record">
          <a :href="routeUri('admin.payment.withdraw.request.detail', {withdrawl_hash: record.hash})" class="fw-bold text-black">
            {{ record.partner_id.code }}
          </a>
        </span>
        <span slot="first_balance" slot-scope="record">Rp. {{ formatPrice(record.first_balance) }}</span>
        <span slot="amount" slot-scope="record">
          <template v-if="record.amount != 0">
            Rp. {{ formatPrice(record.amount) }}
          </template>
          <template v-else>
            -
          </template>
        </span>
        <span slot="created_at" slot-scope="record">{{ moment(record.created_at).format("ddd, DD MMM YYYY") }}</span>
        <span slot="action" slot-scope="record">
          <template v-if="record.status == 'approved'">
            <div class="tag green">
              Approved
            </div>
          </template>
          <template v-else>
            <div class="tag yellow">
              Request
            </div>
          </template>
        </span>
      </a-table>
    </template>
  </content-layout>
</template>
<script>
import requestColumns from "../../../../../config/table/withdraw/request";
import ContentLayout from "../../../../../layouts/content-layout.vue";

export default {
  components: {
    ContentLayout,
  },
  data: () => ({
    lists: [],
    loading: false,
    total_request: '',
    request_disbursment: {},
    filter_status: null,
    filter: {
      code: null,
      start_date: null,
      end_date: null,
    },
    codes: [],
    requestColumns,
    loading: false,
    numbers: [],
    data_excel: null
  }),
  created() {
    this.getDisbursmentList()
    this.getTotalRequest()
    this.getRequestDisbursment()
    this.filterCode()
  },
  computed: {
    filteredItems() {
      return this.lists.filter(item => {
        if(this.filter_status != null){
          return item.status == this.filter_status
        }else if (this.filter.code != null){
          return item.partner_id.code == this.filter.code
        }else{
          return true
        }
      })
    }
  },
  methods: {
    formatPrice(value) {
        let val = (value/1).toFixed(2).replace('.', ',')
        return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
    },
    getDisbursmentList(){
      this.loading = true
      axios.get(`https://api.staging.trawlbens.co.id/internal/finance/list`, {
        headers: {
            Authorization: 'Bearer 33550|wAGPf6c1hwsIHEzmvsaewakN1wKy0Sd2FVGSTkSi'
        }
      })
      .then((res)=>{
          this.loading = false
          this.lists = res.data.data
          let numbering = 1;
          this.lists.forEach((o, k) => {
              o.number = numbering++;
          });
      }).catch(function (error) {
          console.error(error);
          this.loading = false
      });
    },
    getTotalRequest(){
      this.loading = true
      axios.get(`https://api.staging.trawlbens.co.id/internal/finance/count/amount`, {
        headers: {
            Authorization: 'Bearer 33550|wAGPf6c1hwsIHEzmvsaewakN1wKy0Sd2FVGSTkSi'
        }
      })
      .then((res)=>{
          this.total_request = res.data.data
          this.loading = false
      }).catch(function (error) {
          console.error(error);
          this.loading = false
      });
    },
    getRequestDisbursment(){
      this.loading = true
      axios.get(`https://api.staging.trawlbens.co.id/internal/finance/count`, {
        headers: {
            Authorization: 'Bearer 33550|wAGPf6c1hwsIHEzmvsaewakN1wKy0Sd2FVGSTkSi'
        }
      })
      .then((res)=>{
          this.request_disbursment = res.data.data
          this.loading = false
      }).catch(function (error) {
          console.error(error);
          this.loading = false
      });
    },
    filterCode(){
      this.loading = true
      axios.get(`https://api.staging.trawlbens.co.id/internal/finance/list/partners`, {
        headers: {
            Authorization: 'Bearer 33550|wAGPf6c1hwsIHEzmvsaewakN1wKy0Sd2FVGSTkSi'
        }
      })
      .then((res)=>{
          this.codes = res.data.data
      }).catch(function (error) {
          console.error(error);
          this.loading = false
      });
    },
    filterDate(){
      if(this.filter.start_date == null || this.filter.end_date == null){
        this.loading = true
        axios.get(`https://api.staging.trawlbens.co.id/internal/finance/list`, {
          headers: {
            Authorization: 'Bearer 33550|wAGPf6c1hwsIHEzmvsaewakN1wKy0Sd2FVGSTkSi'
          }
        })
        .then((res)=>{
            this.loading = false
            this.lists = res.data.data
            this.filterCode()
            let numbering = 1;
            this.lists.forEach((o, k) => {
                o.number = numbering++;
            });
        }).catch(function (error) {
            console.error(error);
            this.loading = false
        });
      }else{
        this.loading = true
        axios.get(`https://api.staging.trawlbens.co.id/internal/finance/find/date`, {
          params: {
            start_date: this.filter.start_date,
            end_date: this.filter.end_date
          },
          headers: {
            Authorization: 'Bearer 33550|wAGPf6c1hwsIHEzmvsaewakN1wKy0Sd2FVGSTkSi'
          }
        })
        .then((res)=>{
            this.lists = res.data.data
            this.filterCode()
            let numbering = 1;
            this.lists.forEach((o, k) => {
                o.number = numbering++;
            });
            this.loading = false
        }).catch(function (error) {
            console.log(error);
            this.loading = false
        });
      }
    },
    exportData(){
      axios.get(`https://api.staging.trawlbens.co.id/internal/finance/report`, {
        params: {
          start: this.filter.start_date,
          end: this.filter.end_date
        },
        headers: {
          Authorization: 'Bearer 33550|wAGPf6c1hwsIHEzmvsaewakN1wKy0Sd2FVGSTkSi'
        },
        responseType: 'blob'
      })
      .then((res)=>{
        this.data_excel = res.data
        const url = window.URL.createObjectURL(new Blob([res.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'file.xls');
        document.body.appendChild(link);
        link.click();

      }).catch(function (error) {
          console.error(error);
      });
    }
  },
};
</script>

<style lang="scss">
  .mb-0{
    margin-bottom: 0px !important;
  }
  .title-price{
    font-size: 25px !important;
  }
  .mt-date{
    margin-top: 5px;
  }
  .h-button{
    height: 37px !important;
  }
  .tag{
    width: 100px;
    padding: 4px 12px 7px 12px;
    border-radius: 10px;
    color: #fff;
    font-size: 13px;
    text-align: center;
    &.green{
      background-color: #3D8824;
    }
    &.yellow{
      background-color: #FB9727;
    }
  }
  .text-black{
    color: #000;
  }
  .text-gray{
    color: #61616A;
  }
</style>
