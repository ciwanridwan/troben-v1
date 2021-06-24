<template>
  <a-radio-group v-model="partner_hash" :style="{ width: '100%' }">
    <a-space direction="vertical" size="large" :style="{ width: '100%' }">
      <partner-radio-button
        v-for="(partner, index) in partners"
        :key="index"
        :partner="partner"
        :currentChecked="partner_hash"
      />
    </a-space>
  </a-radio-group>
</template>
<script>
import partnerRadioButton from "./partner-radio-button.vue";
export default {
  components: { partnerRadioButton },
  props: {
    value: null,
    partners: {
      type: Array,
      default: () => {
        return [];
      },
    },
  },
  data() {
    return {
      partner_hash: null,
    };
  },
  watch: {
    partner_hash: function (value) {
      this.$emit("input", value);
    },
  },
};
</script>
