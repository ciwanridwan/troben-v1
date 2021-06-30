<template>
  <a-form-model ref="formRules" :model="form" :rules="rules">
    <a-form-model-item prop="type">
      <a-select v-model="form.type" style="width: 100%">
        <a-select-option
          v-for="(type, index) in types"
          :key="index"
          :value="type.type"
        >
          {{ type.title }}
        </a-select-option>
      </a-select>
    </a-form-model-item>
  </a-form-model>
</template>
<script>
import { types } from "../../../data/partnerType";
export default {
  data() {
    return {
      types,
      form: {
        type: null
      },
      rules: {
        type: [{ required: true }]
      },
      valid: false
    };
  },
  watch: {
    form: {
      handler: function(value) {
        this.$refs.formRules.validate().then(valid => {
          if (valid) {
            this.$emit("input", this.form.type);
            this.valid = true;
          }
        });
      },
      deep: true
    }
  }
};
</script>
