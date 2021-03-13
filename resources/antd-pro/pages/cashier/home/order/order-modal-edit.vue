<template>
  <div>
    <edit-button @click="visible = true"></edit-button>
    <a-modal v-model="visible" :width="840" centered>
      <template slot="title">
        <h3><b>Edit Barang</b></h3>
      </template>

      <a-row type="flex" :gutter="10">
        <a-col :span="14">
          <!-- sender address -->
          <a-space align="start">
            <send-icon></send-icon>
            <address-component></address-component>
          </a-space>

          <a-radio-group>
            <a-space direction="vertical">
              <a-card v-for="index in 2" :key="index">
                <a-space size="small" align="start">
                  <receive-icon></receive-icon>

                  <div>
                    <address-component :receiver="true"></address-component>
                    <order-estimation></order-estimation>
                  </div>

                  <a-radio :value="index"> </a-radio>
                </a-space>
              </a-card>
            </a-space>
          </a-radio-group>
        </a-col>
        <a-col :span="10">
          <!-- description -->
          <a-form-model :model="form" ref="formRules" layout="vertical">
            <a-form-model-item ref="description" prop="description">
              <template slot="label">
                <h3>Deskripsi Barang</h3>
              </template>
              <a-input v-model="form.description"></a-input>
            </a-form-model-item>

            <!-- dimension -->
            <a-row type="flex" :gutter="[10, 10]">
              <!-- label -->
              <a-col :span="8">
                <h3>Panjang (cm)</h3>
              </a-col>
              <a-col :span="8">
                <h3>Lebar (cm)</h3>
              </a-col>
              <a-col :span="8">
                <h3>Tinggi (cm)</h3>
              </a-col>

              <!-- form -->
              <a-col :span="8">
                <a-form-model-item ref="length" prop="length">
                  <a-input v-model="form.length"></a-input>
                </a-form-model-item>
              </a-col>
              <a-col :span="8">
                <a-form-model-item ref="width" prop="width">
                  <a-input v-model="form.width"></a-input>
                </a-form-model-item>
              </a-col>
              <a-col :span="8">
                <a-form-model-item ref="height" prop="height">
                  <a-input v-model="form.height"></a-input>
                </a-form-model-item>
              </a-col>
            </a-row>

            <!-- actual weight -->
            <a-form-model-item ref="actual_weight" prop="actual_weight">
              <template slot="label">
                <h3>Berat Aktual</h3>
              </template>
              <a-input v-model="form.actual_weight"></a-input>
            </a-form-model-item>

            <!-- package number -->
            <a-form-model-item ref="package_number" prop="package_number">
              <template slot="label">
                <h3>Jumlah Barang</h3>
              </template>
              <a-input v-model="form.package_number"></a-input>
            </a-form-model-item>

            <!-- package price -->
            <a-form-model-item ref="package_price" prop="package_price">
              <template slot="label">
                <h3>Harga Barang</h3>
              </template>
              <a-input v-model="form.package_price"></a-input>
            </a-form-model-item>

            <!-- Insurance -->
            <a-form-model-item ref="insurance" prop="insurance">
              <a-checkbox v-model="form.insurance">
                Asuransi
              </a-checkbox>
            </a-form-model-item>

            <!-- packaging -->
            <a-form-model-item ref="packaging" prop="packaging">
              <a-radio-group v-model="form.packaging">
                <a-space direction="vertical">
                  <a-radio :value="false">
                    Tanpa Packing
                  </a-radio>
                  <a-radio :value="true">
                    Pakai Packing
                  </a-radio>
                </a-space>
              </a-radio-group>
            </a-form-model-item>

            <!-- packaging_type -->
            <a-form-model-item
              v-show="form.packaging"
              ref="packaging_type"
              prop="packaging_type"
            >
              <a-checkbox-group>
                <a-row type="flex">
                  <a-col :span="12">
                    <a-checkbox value="wood">
                      Kayu
                    </a-checkbox>
                  </a-col>
                  <a-col :span="12">
                    <a-checkbox value="box">
                      Kardus
                    </a-checkbox>
                  </a-col>
                  <a-col :span="12">
                    <a-checkbox value="plastic">
                      Plastik
                    </a-checkbox>
                  </a-col>
                  <a-col :span="12">
                    <a-checkbox value="pallate">
                      Pallate
                    </a-checkbox>
                  </a-col>
                  <a-col :span="12">
                    <a-checkbox value="bubble_wrap">
                      Bubble Wrap
                    </a-checkbox>
                  </a-col>
                  <a-col :span="12">
                    <a-checkbox value="sand_bag">
                      Karung
                    </a-checkbox>
                  </a-col>
                </a-row>
              </a-checkbox-group>
            </a-form-model-item>
          </a-form-model>
        </a-col>
      </a-row>
    </a-modal>
  </div>
</template>
<script>
import editButton from "../../../../components/button/edit-button.vue";
import { SendIcon, ReceiveIcon } from "../../../../components/icons";
import AddressComponent from "../../../../components/orders/address-component.vue";
import OrderEstimation from "../../../../components/orders/order-estimation.vue";
export default {
  components: {
    editButton,
    SendIcon,
    ReceiveIcon,
    AddressComponent,
    OrderEstimation
  },
  data() {
    return {
      visible: false,
      form: {
        description: null,
        length: null,
        height: null,
        width: null,
        actual_weight: null,
        package_number: null,
        package_price: null,
        insurance: null,
        packaging: null,
        packaging_type: null
      }
    };
  }
};
</script>
