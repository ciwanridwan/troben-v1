<template>
  <a-form-model ref="formRules" :rules="rules">
    <a-form-model-item prop="insurance">
      <a-space>
        <a-checkbox v-model="form.insurance">
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
      form: {
        insurance: false,
      },
      rules: {
        insurance: [{ required: false }],
      },
    };
  },
};
</script>
