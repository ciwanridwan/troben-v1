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
        <a-icon
          :component="ReceiveIcon"
          :style="{ 'font-size': '2rem' }"
        ></a-icon>
      </template>
      <template slot="content">
        <package-address :package="package" type="receiver" />
      </template>
    </order-modal-row-layout>
    <order-modal-row-layout :afterLine="false">
      <template slot="icon">
        <a-icon
          :component="ReceiveIcon"
          :style="{ 'font-size': '2rem' }"
        ></a-icon>
      </template>
      <template slot="content">
        <div
          style="
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 1rem;
          "
        >
          <enlargeable-image
            style="width: 100px !important"
            v-for="(data, index) in bike_image"
            :key="index"
            :src="data.uri"
          ></enlargeable-image>
        </div>
      </template>
    </order-modal-row-layout>
  </div>
</template>
<script>
import orderModalRowLayout from "../orders/order-modal-row-layout.vue";
import PackageAddress from "./package-address.vue";
import EnlargeableImage from "@diracleo/vue-enlargeable-image";
import { SendIcon, ReceiveIcon } from "../icons";
export default {
  props: {
    package: {
      type: Object,
      default: () => {},
    },
  },
  components: { orderModalRowLayout, PackageAddress, EnlargeableImage },
  data() {
    return {
      SendIcon,
      ReceiveIcon,
    };
  },
  computed: {
    isBike() {
      return this.package?.order_type;
    },
    bike_image() {
      return this.package?.attachments;
    },
  },
};
</script>
