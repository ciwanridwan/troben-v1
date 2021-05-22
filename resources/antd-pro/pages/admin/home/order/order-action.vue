<template>
  <div v-if="record.status == 'accepted' && record.payment_status == 'pending'">
    <modal-payment-confirm :afterConfirm="afterAction" :record="record" />
  </div>
  <div v-else-if="record.status === 'created'">
    <a-space>
      <a-button type="danger" ghost>Cancel</a-button>
      <modal-assign-mitra :order="record" :afterAssign="afterAction" />
    </a-space>
  </div>
  <div v-else>test</div>
</template>
<script>
import trawlModalConfirm from "../../../../components/trawl-modal-confirm.vue";
import ModalAssignMitra from "./modal-assign-mitra.vue";
import ModalPaymentConfirm from "./modal-payment-confirm.vue";

export default {
  components: { trawlModalConfirm, ModalPaymentConfirm, ModalAssignMitra },
  props: {
    record: {
      type: Object,
      default: () => {}
    },
    afterAction: {
      type: Function,
      default: () => {}
    }
  }
};
</script>
