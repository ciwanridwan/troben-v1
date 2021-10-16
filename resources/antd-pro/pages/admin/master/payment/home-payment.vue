<template>
  <content-layout title="Finance Dashboard">
    <template slot="content">
      <a-card style="height:50vh; width:80vw">

        <a-list :grid="{ gutter: 16, column: 2 }">
          <a-list-item style="padding: 8px">
            <div>
              <PlanetChart style="height:20vh; width:40vw"/>
            </div>
          </a-list-item>
          <a-list-item style="padding: 8px">
            <div>
              <h3>testa</h3>
            </div>
          </a-list-item>
        </a-list>
      </a-card>
      <br/>

      <template>
        <a-list :grid="{ gutter: 16, column: 2 }">
          <a-list-item style="padding: 8px">
            <a-card>
              <a-row
                :gutter="[30, 10]">
                <a-col :span="10">
                  <a-row type="flex" align="top" :gutter="[5, 5]">
                    <a-col>
                      <h3>Pendapatan Mitra</h3>
                      <a-date-picker :size="size"></a-date-picker>
                    </a-col>
                  </a-row>
                  <a-row type="flex" align="middle" :gutter="[5, 5]">
                    <a-col>
                      <h3>Rp. 52000400</h3>
                      <h3>Rp. 2000400</h3>
                    </a-col>
                  </a-row>
                  <a-row type="flex" align="bottom" :gutter="[5, 5]">
                    <a-col>
                      <h3>Pendapatan hari sebelumnya</h3>
                      <h3>Rp. 50000000</h3>
                    </a-col>
                  </a-row>
                </a-col>
                <a-col :span="14">
                  <a-row style="padding:5px">
                    <a-card>
                      <a-row :gutter="16">
                        <a-col :span="10">
                          <h4>MS</h4>
                          <h5>M.Space</h5>
                        </a-col>
                        <a-col :span="14">
                          <h4 align="middle" style="color: green">
                            Rp. 6000000
                          </h4>
                        </a-col>
                      </a-row>
                    </a-card>
                  </a-row>

                  <a-row style="padding:5px">
                    <a-card>
                      <a-row :gutter="16">
                        <a-col :span="10">
                          <h4>MB</h4>
                          <h5>M.Business</h5>
                        </a-col>
                        <a-col :span="14">
                          <h4 align="middle" style="color: green">
                            Rp. 6000000
                          </h4>
                        </a-col>
                      </a-row>
                    </a-card>
                  </a-row>

                  <a-row style="padding:5px">
                    <a-card>
                      <a-row :gutter="16">
                        <a-col :span="10">
                          <h4>MPW</h4>
                          <h5>M.Pool Warehouse</h5>
                        </a-col>
                        <a-col :span="14" style="align-content: center" type="flex">
                          <h4 style="color: green">
                            Rp. 33333333
                          </h4>
                        </a-col>
                      </a-row>
                    </a-card>
                  </a-row>

                  <a-row style="padding:5px">
                    <a-card>
                      <a-row :gutter="16">
                        <a-col :span="10">
                          <h4>MT</h4>
                          <h5>M.Transpoter</h5>
                        </a-col>
                        <a-col :span="14">
                          <h4 align="middle" style="color: green">
                            Rp. 6000000
                          </h4>
                        </a-col>
                      </a-row>
                    </a-card>
                  </a-row>
                </a-col>
              </a-row>
            </a-card>
          </a-list-item>
          <a-list-item style="padding: 8px">
            <a-card>
              <a-row
                type="flex"
                justify="space-between"
                :gutter="[64, 10]">
                <a-col :span="11">
                  <a-row type="flex" align="top":span="8">
                    <h3>Pendapatan Mitra</h3>
                    <a-date-picker :size="size"></a-date-picker>
                  </a-row>
                  <a-row type="flex"  align="middle":span="8" >
                    <h3>Rp. 52000400</h3>
                    <br/>
                    <h3>Rp. 2000400</h3>
                  </a-row>
                  <a-row type="flex" align="bottom":span="8" >
                    <h3>Pendapatan hari sebelumnya</h3>
                    <h3>Rp. 50000000</h3>
                  </a-row>
                </a-col>
                <a-col :span="13">
                  <a-row style="padding:5px">
                    <a-card>
                      <a-row :gutter="16">
                        <a-col :span="12">
                          <h3>MS</h3>
                          <h3>M.Space</h3>
                        </a-col>
                        <a-col :span="12">
                          <h3>
                            <b>Rp. 6000000</b>
                          </h3>
                        </a-col>
                      </a-row>
                    </a-card>
                  </a-row>

                  <a-row style="padding:5px">
                    <a-card>
                      <a-row :gutter="16">
                        <a-col :span="12">
                          <h3>MB</h3>
                          <h3>M.Business</h3>
                        </a-col>
                        <a-col :span="12">
                          <h3>
                            <b>Rp. 6000000</b>
                          </h3>
                        </a-col>
                      </a-row>
                    </a-card>
                  </a-row>

                  <a-row style="padding:5px">
                    <a-card>
                      <a-row :gutter="16">
                        <a-col :span="12">
                          <h3>MPW</h3>
                          <h3>M.Pool Warehouse</h3>
                        </a-col>
                        <a-col :span="12">
                          <h3>
                            <b>Rp. 6000000</b>
                          </h3>
                        </a-col>
                      </a-row>
                    </a-card>
                  </a-row>

                  <a-row style="padding:5px">
                    <a-card>
                      <a-row :gutter="16">
                        <a-col :span="12">
                          <h3>MT</h3>
                          <h3>M.Transpoter</h3>
                        </a-col>
                        <a-col :span="12">
                          <h2>
                            <b>Rp. 6000000</b>
                          </h2>
                        </a-col>
                      </a-row>
                    </a-card>
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
<script>
import contentLayout from "../../../../layouts/content-layout.vue";
import paymentColumns from "../../../../config/table/payment";
import PlanetChart from '../../../../components/PlanetChart.vue';
export default {
  components: {
    contentLayout,
    PlanetChart
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
    paymentColumns,
    size : 'large',
    options: {
      chart: {
        id: 'vuechart-example'
      },
      xaxis: {
        categories: [1991, 1992, 1993, 1994, 1995, 1996, 1997, 1998]
      }
    },
    series: [{
      name: 'series-1',
      data: [30, 40, 45, 50, 49, 60, 70, 91]
    }]
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
