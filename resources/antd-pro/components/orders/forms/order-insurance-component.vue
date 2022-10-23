<template>
  <a-form-model ref="formRules" :rules="rules" :model="value">
    <a-form-model-item prop="is_insured">
      <a-space direction="vertical">
        <a-space>
          <a-checkbox v-model="value.is_insured">
            Ganti rugi 90% jika produk hilang akibat insiden saat pengiriman
            barang. Biaya…
          </a-checkbox>
          <a-icon
            :component="InformationCircleIcon"
            :style="{ cursor: 'pointer' }"
          />
        </a-space>
        <a-form-model-item
          v-if="value.is_insured && isMotor == 'bike'"
          label="Asuransi Motor"
        >
          <a-input
            v-model.number="value.price"
            type="number"
            size="large"
            placeholder="Masukan harga motor anda"
          ></a-input>
        </a-form-model-item>
      </a-space>
    </a-form-model-item>
  </a-form-model>
</template>
<script>
import { InformationCircleIcon } from "../../icons";
export default {
  data() {
    return {
      InformationCircleIcon,

      rules: {
        is_insured: [{ required: false }],
      },
    };
  },
  props: {
    defaultValue: {
      type: Object,
      default: () => {},
    },
    value: {
      type: Object,
      default: () => {
        return {
          is_insured: false,
          price: null,
        };
      },
    },
    isMotor: String,
  },
  methods: {
    setDefaultValue() {
      Object.keys(this.form).forEach((k) => {
        this.form[k] = this.defaultValue[k];
      });
    },
  },
  watch: {
    value: {
      handler: function (value) {
        if (!this.value.is_insured) {
          value.price = null;
        }
        this.$emit("change", value);
      },
      deep: true,
    },
  },
};
</script>
