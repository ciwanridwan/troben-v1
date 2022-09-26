<template>
  <a-space v-if="type === 'sender'" direction="vertical" :size="1">
    <span v-if="title">Pengirim</span>
    <span class="trawl-text-bolder">{{ sender_name }}</span>
    <span>{{ sender_phone }}</span>
    <p class="trawl-text-normal">{{ sender_address }}</p>
  </a-space>
  <a-space v-else direction="vertical" :size="1">
    <span v-if="title">Penerima</span>
    <span class="trawl-text-bolder">{{ receiver_name }}</span>
    <span>{{ receiver_phone }}</span>
    <p class="trawl-text-normal">{{ receiver_address }}</p>
  </a-space>
</template>
<script>
import orderModalRowLayout from "../orders/order-modal-row-layout.vue";
export default {
  props: {
    delivery: {
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
    receiver_address() {
      return (
        this.delivery?.partner?.geo_address ?? this.delivery?.partner?.address
      );
    },
    receiver_phone() {
      return this.delivery?.partner?.phone;
    },
    receiver_name() {
      return this.delivery?.partner?.code;
    },

    sender_address() {
      return (
        this.delivery?.origin_partner?.geo_address ??
        this.delivery?.origin_partner?.address
      );
    },
    sender_phone() {
      return this.delivery?.origin_partner?.phone;
    },
    sender_name() {
      return this.delivery?.origin_partner?.code;
    },
  },
};
</script>
