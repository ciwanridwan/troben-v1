<template>
  <a-space v-if="type === 'sender'" direction="vertical">
    <span v-if="title" :style="textStyle">Pengirim</span>
    <span class="trawl-text-bolder" :style="textStyle">{{ sender_name }}</span>
    <span :style="textStyle">
      {{ sender_phone }}
    </span>
    <p class="trawl-text-normal" :style="textStyle">
      {{ sender_address }}
    </p>
    <span :style="textStyle">
      Kode pos : {{ sender_zip_code }}
    </span>
  </a-space>
  <a-space v-else direction="vertical">
    <span v-if="title" :style="textStyle">
      Penerima
    </span>
    <span class="trawl-text-bolder" :style="textStyle">
      {{ receiver_name }}
    </span>
    <span :style="textStyle">
      {{ receiver_phone }}
    </span>
    <p class="trawl-text-normal" :style="textStyle">
      {{ receiver_address }}
    </p>
    <span :style="textStyle">
      Kode pos : {{ receiver_zip_code }}
    </span>
  </a-space>
</template>
<script>
import { getOriginAddress, getDestinationAddress } from "../../functions/orders";
import orderModalRowLayout from "../orders/order-modal-row-layout.vue";
export default {
  data() {
    return {
      textStyle: { "font-size": ".57rem" },
    }
  },
  props: {
    package: {
      type: Object,
      default: () => {},
    },
    type: {
      type: String,
      default: "sender",
    },
    title: {
      type: Boolean,
      default: true,
    },
  },
  components: { orderModalRowLayout },
  computed: {
    receiver_zip_code() {
      return this.package?.destination_sub_district?.zip_code;
    },
    receiver_address() {
      return this.package?.receiver_address + getDestinationAddress(this.package);
    },
    receiver_phone() {
      return this.package?.receiver_phone;
    },
    receiver_name() {
      return this.package?.receiver_name;
    },

    sender_zip_code() {
      return this.package?.origin_sub_district?.zip_code;
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
