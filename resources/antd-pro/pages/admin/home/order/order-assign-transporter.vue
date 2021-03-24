<template>
  <div>
    <a-button
      type="primary"
      class="trawl-button-success"
      @click="visible = true">
      Assign Mitra
    </a-button>
    <a-modal v-model="visible" :width="840" centered :footer="null">
      <template slot="title">
        <h3><b>Assign Mitra</b></h3>
      </template>

      <a-row type="flex">
        <a-col :span="14" class="trawl-border-right" style="padding-right:24px">
          <a-space direction="vertical" :size="24">
            <!-- sender address -->
            <a-row type="flex">
              <a-col :span="iconSize">
                <send-icon></send-icon>
              </a-col>
              <a-col :span="leftSize">
                <address-component>
                  <template slot="address">
                    <p>
                      MPW-JKT-0001 08817009000 Gudang Karplindo, Jl. Raya Bogor
                      Km No.27, RT.9/RW.4
                    </p>
                  </template>
                </address-component>
              </a-col>
            </a-row>

            <!-- receiver address -->
            <a-row type="flex">
              <a-col :span="iconSize">
                <receive-icon></receive-icon>
              </a-col>
              <a-col :span="leftSize">
                <address-component :receiver="true">
                  <template slot="address">
                    <p>
                      MPW-UPG-0001 085299128394 Jl. Raya Kapasa, Komp. Pagodam,
                      Blok F2, Blok F15 - 16, Daya, Kec. Biringkanaya,
                    </p>
                  </template>
                </address-component>
              </a-col>
            </a-row>

            <a-row>
              <a-col :span="iconSize"> </a-col>
              <a-col :span="leftSize">
                <h5 class="trawl-text-normal">Jumlah Kg: <b>70 Kg</b></h5>
              </a-col>
            </a-row>
          </a-space>
        </a-col>
        <a-col :span="10">
          <a-layout>
            <a-layout-content class="trawl-bg-white">
              <!-- description -->
              <a-space direction="vertical">
                <div style="padding:0px 24px">
                  <h3>Pilih Mitra</h3>
                  <a-input-search v-model="search"> </a-input-search>
                </div>

                <a-divider></a-divider>

                <a-radio-group style="padding:0px 24px">
                  <a-space direction="vertical">
                    <a-card v-for="index in 2" :key="index">
                      <a-space direction="vertical">
                        <a-row type="flex">
                          <a-col :span="20">
                            <a-space direction="vertical">
                              <!-- PARTNER NAME -->
                              <h3 class="trawl-text-normal">
                                MB-BDJ-0003
                              </h3>
                              <!-- OWNER NAME -->
                              <h3>
                                Faris Ferdian Akbar
                              </h3>
                            </a-space>
                          </a-col>
                          <a-col :span="4" class="trawl-text-right">
                            <a-radio :value="index"> </a-radio>
                          </a-col>
                        </a-row>
                        <a-space align="start">
                          <home-icon></home-icon>
                          <p>
                            MPW-JKT-0001 08817009000 Gudang Karplindo, Jl. Raya
                            Bogor Km No.27, RT.9/RW.4
                          </p>
                        </a-space>
                      </a-space>
                    </a-card>
                  </a-space>
                </a-radio-group>

                <a-divider></a-divider>
              </a-space>
            </a-layout-content>
            <a-layout-footer class="trawl-bg-white" style="padding:24px">
              <a-button @click="onOk" class="trawl-button-success" block>Tugaskan</a-button>
            </a-layout-footer>
          </a-layout>
        </a-col>
      </a-row>
    </a-modal>
  </div>
</template>
<script>
import editButton from "../../../../components/button/edit-button.vue";
import { SendIcon, ReceiveIcon, HomeIcon } from "../../../../components/icons";

import AddressComponent from "../../../../components/orders/address-component.vue";
import OrderEstimation from "../../../../components/orders/order-estimation.vue";
export default {
  name: 'OrderAssignTransporter',
  components: {
    editButton,
    SendIcon,
    ReceiveIcon,
    AddressComponent,
    OrderEstimation,
    HomeIcon
  },
  data() {
    return {
      visible: false,
      search: null,
      iconSize: 4,
      form: {}
    };
  },
  computed: {
    leftSize() {
      return 24 - this.iconSize;
    }
  },
  methods: {
    onOk() {
      this.visible = false;
    }
  }
};
</script>
