<template>
  <div>
    <order-modal-row-layout :afterLine="false">
      <template slot="icon">
        <a-icon :component="SendIcon" :style="{ 'font-size': '2rem' }"></a-icon>
      </template>
      <template slot="content"> </template>
    </order-modal-row-layout>
    <order-modal-row-layout :afterLine="false">
      <template slot="icon">
        <a-icon
          :component="ReceiveIcon"
          :style="{ 'font-size': '2rem' }"
        ></a-icon>
      </template>
      <template slot="content"> </template>
    </order-modal-row-layout>
  </div>
</template>
<script>
import { SendIcon, ReceiveIcon } from "../icons";
import orderModalRowLayout from "../orders/order-modal-row-layout.vue";
export default {
  props: {
    delivery: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      SendIcon,
      ReceiveIcon
    };
  },
  components: { orderModalRowLayout }
};
</script>
