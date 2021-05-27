<template>
  <div>
    <edit-button @click="show"></edit-button>
    <a-modal v-model="visible" centered @ok="onOk">
      <template slot="title">
        <h3><b>Edit Barang</b></h3>
      </template>

      <a-row type="flex" :gutter="10">
        <!-- sender address -->
        <!-- <a-col :span="14">
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
        </a-col> -->
        <a-col :span="24">
          <!-- desc -->
          <a-form-model :model="form" ref="formRules" layout="vertical">
            <a-form-model-item ref="desc" prop="desc">
              <template slot="label">
                <h3>Deskripsi Barang</h3>
              </template>
              <a-input v-model="form.desc"></a-input>
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
                  <a-input-number v-model="form.length"></a-input-number>
                </a-form-model-item>
              </a-col>
              <a-col :span="8">
                <a-form-model-item ref="width" prop="width">
                  <a-input-number v-model="form.width"></a-input-number>
                </a-form-model-item>
              </a-col>
              <a-col :span="8">
                <a-form-model-item ref="height" prop="height">
                  <a-input-number v-model="form.height"></a-input-number>
                </a-form-model-item>
              </a-col>
            </a-row>

            <!-- actual weight -->
            <a-form-model-item ref="weight" prop="weight">
              <template slot="label">
                <h3>Berat Aktual</h3>
              </template>
              <a-input-number v-model="form.weight"></a-input-number>
            </a-form-model-item>

            <!-- package number -->
            <a-form-model-item ref="qty" prop="qty">
              <template slot="label">
                <h3>Jumlah Barang</h3>
              </template>
              <a-input-number v-model="form.qty"></a-input-number>
            </a-form-model-item>

            <!-- package price -->
            <a-form-model-item ref="price" prop="price">
              <template slot="label">
                <h3>Harga Barang</h3>
              </template>
              <a-input-number v-model="form.price"></a-input-number>
            </a-form-model-item>

            <!-- is_Insured -->
            <a-form-model-item ref="is_insured" prop="is_insured">
              <a-checkbox v-model="form.is_insured">
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
              <a-checkbox-group v-model="form.packaging_type">
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
  props: {
    item: {
      type: Object,
      default: () => {}
    },
    updateOrderItem: {
      type: Function,
      default: () => {}
    }
  },
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
        desc: null,
        length: null,
        height: null,
        width: null,
        weight: null,
        qty: null,
        price: null,
        is_insured: null,
        packaging: null,
        packaging_type: null
      }
    };
  },
  watch: {
    "form.packaging_type": value => {
      console.log(value);
    }
  },
  methods: {
    onOk() {
      this.updateOrderItem();
      this.onCancel();
    },
    onCancel() {
      this.visible = false;
    },
    getHandlings() {
      let handlings = [];
      this.item.handling.forEach(item => {
        handlings.push(item.type);
      });
      this.form.packaging = handlings.length > 0;
      this.form.packaging_type = handlings;
    },
    show() {
      this.getHandlings();
      this.form = {
        ...this.form,
        ...this.item
      };
      this.visible = true;
    }
  }
};
</script>
