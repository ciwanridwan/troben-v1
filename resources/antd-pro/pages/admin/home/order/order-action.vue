<template>
  <div v-if="record.status == 'accepted' && record.payment_status == 'pending'">
    <trawl-modal-confirm :ok="paymentConfirm">
      <template slot="trigger">
        <a-button class="trawl-button-success">Lunas</a-button>
      </template>
      <template slot="text">
        <p>
          Apakah pembayaran telah berhasil dilunaskan?
        </p>
      </template>
    </trawl-modal-confirm>
  </div>
  <div v-else>test</div>
</template>
<script>
import trawlModalConfirm from "../../../../components/trawl-modal-confirm.vue";

export default {
  components: { trawlModalConfirm },
  props: {
    record: {
      type: Object,
      default: () => {}
    }
  },
  mounted() {
    console.log(this.record);
  },
  methods: {
    paymentConfirm() {
      console.log(this.record);
    }
  }
};
</script>
