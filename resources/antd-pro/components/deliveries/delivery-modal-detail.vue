<template>
  <a-space direction="vertical" size="large">
    <order-modal-row-layout :afterLine="false">
      <template slot="icon">
        <a-icon :component="SendIcon" :style="{ 'font-size': '2rem' }"></a-icon>
      </template>
      <template slot="content">
        <partner-modal-address :delivery="delivery" />
      </template>
    </order-modal-row-layout>
    <order-modal-row-layout :afterLine="false">
      <template slot="icon">
        <a-icon :component="ReceiveIcon" :style="{ 'font-size': '2rem' }"></a-icon>
      </template>
      <template slot="content">
        <partner-modal-address :delivery="delivery" type="receiver" />
      </template>
    </order-modal-row-layout>
  </a-space>
</template>
<script>
import { SendIcon, ReceiveIcon } from "../icons";
import orderModalRowLayout from "../orders/order-modal-row-layout.vue";
import PartnerModalAddress from "../partners/partner-modal-address.vue";
export default {
  props: {
    delivery: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      SendIcon,
      ReceiveIcon,
    };
  },
  components: { orderModalRowLayout, PartnerModalAddress },
};
</script>
