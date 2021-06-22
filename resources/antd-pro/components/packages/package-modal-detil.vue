<template>
  <div>
    <order-modal-row-layout :afterLine="false">
      <template slot="icon">
        <a-icon :component="SendIcon" :style="{ 'font-size': '2rem' }"></a-icon>
      </template>
      <template slot="content">
        <a-space direction="vertical">
          <span>Pengirim</span>
          <span class="trawl-text-bold">{{ sender_name }}</span>
          <span>{{ sender_phone }}</span>
          <p>{{ sender_address }}</p>
        </a-space>
      </template>
    </order-modal-row-layout>
    <order-modal-row-layout :afterLine="false">
      <template slot="icon">
        <a-icon :component="ReceiveIcon" :style="{ 'font-size': '2rem' }"></a-icon>
      </template>
      <template slot="content">
        <a-space direction="vertical">
          <span>Penerima</span>
          <span class="trawl-text-bold">{{ receiver_name }}</span>
          <span>{{ receiver_phone }}</span>
          <p>{{ receiver_address }}</p>
        </a-space>
      </template>
    </order-modal-row-layout>
  </div>
</template>
<script>
import { getOriginAddress, getDestinationAddress } from "../../functions/orders";
import { SendIcon, ReceiveIcon } from "../icons";
import orderModalRowLayout from "../orders/order-modal-row-layout.vue";
export default {
  props: {
    package: {
      type: Object,
      default: () => {},
    },
  },
  components: { orderModalRowLayout },
  data() {
    return {
      SendIcon,
      ReceiveIcon,
    };
  },
  computed: {
    receiver_address() {
      return this.package?.receiver_address + getDestinationAddress(this.package);
    },
    receiver_phone() {
      return this.package?.receiver_phone;
    },
    receiver_name() {
      return this.package?.receiver_name;
    },

    sender_address() {
      return this.package?.sender_address + getOriginAddress(this.package);
    },
    sender_phone() {
      return this.package?.sender_phone;
    },
    sender_name() {
      return this.package?.sender_name;
    },
  },
};
</script>
