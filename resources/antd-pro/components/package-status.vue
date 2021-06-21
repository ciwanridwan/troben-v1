<template>
  <div>
    <a-badge :status="currentStatus.messageType" text=""></a-badge>
    <span :class="[`trawl-status-${currentStatus.messageType}`]">{{
      currentStatus.message
    }}</span>
  </div>
</template>
<script>
import { getMessageByStatus } from "../functions/packageStatus";
export default {
  props: ["record"],
  computed: {
    currentStatus() {
      let status = getMessageByStatus(this.record.status);
      return status;
    },
  },
};
</script>
