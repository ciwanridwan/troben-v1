<template>
  <a-form-model ref="formRules" :rules="rules" :model="value">
    <a-form-model-item prop="insurance">
      <a-space>
        <a-checkbox v-model="value.insurance">
          Ganti rugi 90% jika produk hilang akibat insiden saat pengiriman barang. Biaya…
        </a-checkbox>
        <a-icon :component="InformationCircleIcon" :style="{ cursor: 'pointer' }" />
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
        insurance: [{ required: false }],
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
          insurance: false,
        };
      },
    },
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
        this.$emit("change", value);
      },
      deep: true,
    },
  },
};
</script>
