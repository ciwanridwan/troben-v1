<template>
  <a-space v-if="type === 'sender'" direction="vertical" :size="0.1">
    <span v-if="title" :style="{ 'font-size': '.7rem' }">Pengirim</span>
    <span class="trawl-text-bolder" :style="textStyle">{{ sender_name }}</span>
    <span class="trawl-text-bolder" :style="textStyle">
      {{ sender_phone }}
    </span>
    <p class="trawl-text-bolder" :style="textStyle">
      {{ sender_address }}
    </p>
    <span :style="textStyle"> Kode pos : {{ sender_zip_code }} </span>
    <span :style="textStyle"> <strong>Note :</strong> {{ sender_note }} </span>
  </a-space>
  <a-space v-else direction="vertical" :size="0.1">
    <span v-if="title" :style="{ 'font-size': '.7rem' }"> Penerima </span>
    <span class="trawl-text-bolder" :style="textStyle">
      {{ receiver_name }}
    </span>
    <span class="trawl-text-bolder" :style="textStyle">
      {{ receiver_phone }}
    </span>
    <p class="trawl-text-bolder" :style="textStyle">
      {{ receiver_address }}
    </p>
    <span :style="textStyle"> Kode pos : {{ receiver_zip_code }} </span>
    <span :style="textStyle"> <strong>Note :</strong> {{ receiver_note }} </span>
  </a-space>
</template>
<script>
import {
  getOriginAddress,
  getDestinationAddress,
} from "../../functions/orders";
import orderModalRowLayout from "../orders/order-modal-row-layout.vue";
export default {
  data() {
    return {
      textStyle: { "font-size": ".7rem" },
    };
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
      return (
        this.package?.receiver_address +
        ", Kota: " +
        getDestinationAddress(this.package)
      );
    },
    receiver_phone() {
      return this.package?.receiver_phone;
    },
    receiver_name() {
      return this.package?.receiver_name;
    },
    receiver_note() {
      return this.package?.receiver_way_point;
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
    sender_note() {
      return this.package?.sender_way_point;
    },
  },
};
</script>
