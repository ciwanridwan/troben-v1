<template>
  <div>
    <a-badge :status="currentStatus.messageType" text=""></a-badge>
    <span :class="[`trawl-status-${currentStatus.messageType}`]">{{
      currentStatus.message
    }}</span>
  </div>
</template>
<script>
import { TYPE_PICKUP, STATUS_ACCEPTED } from "../data/deliveryStatus";
import { getMessageByTypeStatus } from "../functions/deliveryStatus";
export default {
  props: ["record"],
  computed: {
    currentStatus() {
      let status = getMessageByTypeStatus(this.record.type, this.record.status);
      if (this.record.type == TYPE_PICKUP && this.record.status == STATUS_ACCEPTED) {
        status.message += ` ${this.record?.assigned_to?.user?.name}`;
      }
      return status;
    },
  },
};
</script>
