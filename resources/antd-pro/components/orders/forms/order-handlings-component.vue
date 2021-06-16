<template>
  <a-form-model ref="formRules" :model="form" :rules="rules">
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
          <a-col v-for="(value, key) in handlings" :key="key" :span="12">
            <a-checkbox :value="key">
              {{ value }}
            </a-checkbox>
          </a-col>
        </a-row>
      </a-checkbox-group>
    </a-form-model-item>
  </a-form-model>
</template>
<script>
import { handlings } from "../../../data/handlings";
export default {
  components: {},
  props: {
    onChange: {
      type: Function,
      default: () => {}
    }
  },
  data() {
    return {
      handlings,
      form: {
        packaging: false,
        packaging_type: null
      },
      rules: {
        packaging: [{ required: true }],
        packaging_type: [{ required: true }]
      }
    };
  },
  watch: {
    "form.packaging_type": function() {
      this.onChange(this.form);
    },
    "form.packaging": function(value) {
      if (!value) {
        this.form.packaging_type = [];
      }
      this.onChange(this.form);
    }
  }
};
</script>
