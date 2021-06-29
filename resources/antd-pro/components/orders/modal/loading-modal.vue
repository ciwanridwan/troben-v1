<template>
  <trawl-modal-confirm
    v-model="visible"
    :cancelButton="false"
    :okButton="false"
  >
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
    visible: function(value) {
      this.$emit("input", value);
    }
  }
};
</script>
