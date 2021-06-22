<template>
  <a-radio-group v-model="transporter_hash" :style="{ width: '100%' }">
    <a-space direction="vertical" size="large" :style="{ width: '100%' }">
      <transporter-radio-button
        v-for="(transporter, index) in transporters"
        :key="index"
        :transporter="transporter"
        :radioValue="index"
        :currentChecked="transporter_hash"
      />
    </a-space>
  </a-radio-group>
</template>
<script>
import transporterRadioButton from "./transporter-radio-button.vue";
export default {
  components: { transporterRadioButton },
  props: {
    value: null,
    transporters: {
      type: Array,
      default: () => {
        return [];
      },
    },
  },
  data() {
    return {
      transporter_hash: null,
    };
  },
  watch: {
    transporter_hash: function (value) {
      this.$emit("input", value);
    },
  },
};
</script>
