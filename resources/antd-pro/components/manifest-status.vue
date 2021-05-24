<template>
  <div>
    <a-badge :status="currentStatus.messageType" text=""></a-badge>
    <span :class="[`trawl-status-${currentStatus.messageType}`]">{{
      currentStatus.message
    }}</span>
  </div>
</template>
<script>
export default {
  props: ["record"],
  data() {
    return {
      statuses: [
        {
          status: "pending",
          type: "pickup",
          message: "[PICKUP] Menunggu assign transporter",
          messageType: "warning"
        },
        {
          status: "accepted",
          type: "pickup",
          message:
            "[PICKUP] Diterima oleh Driver " +
            this.record?.assigned_to?.user?.name,
          messageType: "warning"
        }
      ]
    };
  },
  computed: {
    currentStatus() {
      let current = this.statuses.find(
        status =>
          this.record.status == status.status && this.record.type == status.type
      );
      if (current) {
        return current;
      } else {
        return {
          message: "[Untracked Status] Please contact developer",
          messageType: "warning"
        };
      }
    }
  }
};
</script>
