<template>
  <div>
    <edit-button @click="show"></edit-button>
    <a-modal v-model="visible" centered @ok="onOk">
      <template slot="closeIcon"
        ><a-icon type="close" @click="onCancel"></a-icon
      ></template>
      <template slot="title">
        <h3><b>Edit Barang</b></h3>
      </template>

      <a-form-model
        :model="form"
        :rules="rules"
        layout="vertical"
        :hideRequiredMark="true"
      >
        <a-row type="flex" :gutter="[12, 12]">
          <a-col :span="24">
            <a-form-model-item prop="desc" label="Deskripsi Barang">
              <a-input v-model="form.desc" placeholder="Deskripsi barang"></a-input>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item prop="length" label="P (cm)">
              <a-input-number
                v-model.number="form.length"
                placeholder="12"
              ></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item prop="width" label="L (cm)">
              <a-input-number
                v-model.number="form.width"
                placeholder="12"
              ></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item prop="height" label="T (cm)">
              <a-input-number
                v-model.number="form.height"
                placeholder="12"
              ></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="24">
            <a-form-model-item prop="weight" label="Berat Aktual">
              <a-input-number
                v-model.number="form.weight"
                placeholder="12"
              ></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="24">
            <a-form-model-item prop="weight" label="Jumlah Paket">
              <a-input-number v-model.number="form.qty" placeholder="12"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="24">
            <a-form-model-item prop="price" label="Harga Barang">
              <a-input-number
                v-model.number="form.price"
                placeholder="12"
              ></a-input-number>
            </a-form-model-item>
          </a-col>
        </a-row>
        <a-form-model-item prop="is_insured">
          <a-checkbox v-model="form.is_insured"> Asuransi </a-checkbox>
        </a-form-model-item>
        <a-form-model-item prop="handling">
          <order-handlings-component v-model="form.handling" />
        </a-form-model-item>
      </a-form-model>

      <template slot="footer">
        <a-button
          type="primary"
          ghost
          class="trawl-button-danger--ghost"
          @click="onCancel"
        >
          Batal
        </a-button>
        <a-button type="success" class="trawl-button-success" @click="onOk">
          Simpan
        </a-button>
      </template>
    </a-modal>
  </div>
</template>
<script>
import editButton from "../button/edit-button.vue";
import OrderHandlingsComponent from "./forms/order-handlings-component.vue";
import OrderInsuranceComponent from "./forms/order-insurance-component.vue";
export default {
  props: {
    value: {
      type: Object,
      default: () => {},
    },
  },
  components: {
    editButton,
    OrderInsuranceComponent,
    OrderHandlingsComponent,
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
        handling: {
          handling: false,
          handling_type: null,
        },
      },
      rules: {
        desc: [{ required: true }],
        length: [{ required: true }],
        height: [{ required: true }],
        width: [{ required: true }],
        weight: [{ required: true }],
        qty: [{ required: true }],
        price: [{ required: true }],
        is_insured: [{ required: false }],
        handling: [{ required: true }],
      },
    };
  },
  methods: {
    show() {
      this.visible = true;
    },
    onCancel() {
      this.setDefaultValue();
      this.visible = false;
    },
    onOk() {
      let form = { ...this.form };
      form.handling = this.form.handling.handling_type;
      this.onCancel();
      this.$emit("submit", form);
    },
    setDefaultValue() {
      let handling = [];
      this.value.handling.forEach((o) => handling.push(o.type));
      this.form = { ...this.value };
      this.form.handling = {
        handling: handling.length > 0,
        handling_type: handling,
      };
    },
  },
  watch: {
    value: {
      handler: function (value) {
        this.setDefaultValue();
      },
    },
    mounted() {
      this.setDefaultValue();
    },
  },
};
</script>
