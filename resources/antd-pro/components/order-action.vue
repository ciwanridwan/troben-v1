<template>
  <div v-if="['pending', 'waiting_for_approval'].includes(record.status)">
    <modal-cancel-confirm :afterConfirm="afterAction" :record="record" />
  </div>
  <div
    v-else-if="record.status == 'accepted' && record.payment_status == 'draft'"
  >
    <modal-cancel-confirm :afterConfirm="afterAction" :record="record" />
  </div>
  <div
    v-else-if="
      record.status == 'accepted' && record.payment_status == 'pending'
    "
  >
    <modal-payment-confirm :afterConfirm="afterAction" :record="record" />
  </div>
  <div v-else-if="record.status === 'created'">
    <a-space>
      <modal-cancel-confirm :afterConfirm="afterAction" :record="record" />
      <modal-assign-mitra :order="record" :afterAssign="afterAction" />
    </a-space>
  </div>
  <div v-else-if="record.status === 'cancel'"></div>
  <div v-else>
    <span class="trawl-text-danger">
      [SYSTEM] Undefined Action
    </span>
  </div>
</template>
<script>
import ModalAssignMitra from "./modal-assign-mitra.vue";
import ModalCancelConfirm from "./modal-cancel-confirm.vue";
import ModalPaymentConfirm from "./modal-payment-confirm.vue";

export default {
  components: {
    ModalPaymentConfirm,
    ModalAssignMitra,
    ModalCancelConfirm
  },
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
