<template>
  <a-form-model ref="formRules" :model="value" :rules="rules">
    <!-- handling -->
    <a-form-model-item ref="handling" prop="handling">
      <a-radio-group v-model="value.handling">
        <a-space direction="vertical">
          <a-radio :value="false"> Tanpa Packing </a-radio>
          <a-radio :value="true"> Pakai Packing </a-radio>
        </a-space>
      </a-radio-group>
    </a-form-model-item>

    <!-- handling_type -->
    <a-form-model-item v-show="value.handling" ref="handling_type" prop="handling_type">
      <a-checkbox-group v-model="value.handling_type">
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
      default: () => {},
    },
    value: {
      type: Object,
      default: () => {
        return {
          handling: false,
          handling_type: null,
        };
      },
    },
    defaultValue: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      handlings,
      rules: {
        handling: [{ required: true }],
        handling_type: [{ required: false }],
      },
    };
  },
  methods: {
    setDefaultValue() {
      Object.keys(this.form).forEach((k) => {
        this.form[k] = this.defaultValue[k];
      });
    },
    updateHandling() {
      this.$emit("change", this.form);
    },
  },
  watch: {
    value: {
      handler: function (value) {
        if (!value.handling) {
          value.handling_type ? (this.value.handling_type = []) : null;
        }
        this.onChange(value);
        this.$emit("change", value);
      },
      deep: true,
    },
  },
};
</script>
