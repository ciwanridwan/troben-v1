<template>
  <trawl-modal-confirm
    v-model="visible"
    :cancelButton="false"
    :okButton="false"
  >
    <template slot="text">
      <a-space direction="vertical">
        <a-icon type="loading" :style="{ 'font-size': '3rem' }" />
        <span>Mohon tunggu </span>
      </a-space>
    </template>
  </trawl-modal-confirm>
</template>
<script>
import trawlModalConfirm from "../../trawl-modal-confirm.vue";
export default {
  props: {
    value: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      visible: false
    };
  },
  components: { trawlModalConfirm },
  watch: {
    value: function(value) {
      this.visible = value;
    },
    visible: function(value) {
      this.$emit("input", value);
    }
  }
};
</script>
