<template>
  <a-space v-if="type === 'sender'" direction="vertical">
    <span v-if="title">Pengirim</span>
    <span class="trawl-text-bolder">{{ sender_name }}</span>
    <span>{{ sender_phone }}</span>
    <p class="trawl-text-normal">{{ sender_address }}</p>
    <span>Kode pos : {{ sender_zip_code }}</span>
  </a-space>
  <a-space v-else direction="vertical">
    <span v-if="title">Penerima</span>
    <span class="trawl-text-bolder">{{ receiver_name }}</span>
    <span>{{ receiver_phone }}</span>
    <p class="trawl-text-normal">{{ receiver_address }}</p>
    <span>Kode pos : {{ receiver_zip_code }}</span>
  </a-space>
</template>
<script>
import { getOriginAddress, getDestinationAddress } from "../../functions/orders";
import orderModalRowLayout from "../orders/order-modal-row-layout.vue";
export default {
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
