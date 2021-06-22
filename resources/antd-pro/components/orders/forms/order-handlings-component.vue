<template>
  <a-form-model ref="formRules" :model="form" :rules="rules">
    <!-- handling -->
    <a-form-model-item ref="handling" prop="handling">
      <a-radio-group v-model="form.handling">
        <a-space direction="vertical">
          <a-radio :value="false"> Tanpa Packing </a-radio>
          <a-radio :value="true"> Pakai Packing </a-radio>
        </a-space>
      </a-radio-group>
    </a-form-model-item>

    <!-- handling_type -->
    <a-form-model-item v-show="form.handling" ref="handling_type" prop="handling_type">
      <a-checkbox-group v-model="form.handling_type">
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
      form: {
        handling: false,
        handling_type: null,
      },
      rules: {
        handling: [{ required: true }],
        handling_type: [{ required: false }],
      },
    };
  },
  watch: {
    form: {
      handler: function (value) {
        if (!value.handling) {
          value.handling_type ? (this.form.handling_type = []) : null;
        }
        this.onChange(value);
        this.$emit("input", value);
        this.$emit("change", value);
      },
      deep: true,
    },
  },
  mounted() {
    this.form = { ...this.value };
  },
};
</script>
