<template>
  <div>
    <order-modal-row-layout :afterLine="false">
      <template slot="icon">
        <a-icon :component="SendIcon" :style="{ 'font-size': '2rem' }"></a-icon>
      </template>
      <template slot="content">
        <package-address :package="package" type="sender" />
      </template>
    </order-modal-row-layout>
    <order-modal-row-layout :afterLine="false">
      <template slot="icon">
        <a-icon :component="ReceiveIcon" :style="{ 'font-size': '2rem' }"></a-icon>
      </template>
      <template slot="content">
        <package-address :package="package" type="receiver" />
      </template>
    </order-modal-row-layout>
  </div>
</template>
<script>
import orderModalRowLayout from "../orders/order-modal-row-layout.vue";
import PackageAddress from "./package-address.vue";
import { SendIcon, ReceiveIcon } from "../icons";
export default {
  props: {
    package: {
      type: Object,
      default: () => {},
    },
  },
  components: { orderModalRowLayout, PackageAddress },
  data() {
    return {
      SendIcon,
      ReceiveIcon,
    };
  },
};
</script>
