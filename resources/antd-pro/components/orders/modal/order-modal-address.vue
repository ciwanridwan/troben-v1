<template>
  <div>
    <order-modal-row-layout>
      <template slot="icon">
        <trawl-red-icon size="3" />
      </template>
      <template slot="content">
        <a-space direction="vertical">
          <span class="trawl-text-bolder"> {{ code }} </span>
          <span>{{ dateSimpleFormat(created_at) }}</span>
        </a-space>
      </template>
    </order-modal-row-layout>

    <order-modal-row-layout>
      <template slot="icon">
        <send-icon />
      </template>
      <template slot="content">
        <package-address :package="package" type="sender" />
      </template>
    </order-modal-row-layout>

    <order-modal-row-layout :afterLine="false">
      <template slot="icon">
        <receive-icon />
      </template>
      <template slot="content">
        <a-space direction="vertical" :size="1">
          <package-address :package="package" type="receiver" />
          <order-estimation v-if="this.price" :price="this.price" />
        </a-space>
      </template>
      <template slot="addon">
        <a-empty v-if="package.attachments[0] == null" />
        <div
          v-else
          style="
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 1rem;
          "
        >
          <enlargeable-image
            style="width: 50px !important"
            v-for="(data, index) in URIImage"
            :key="index"
            :src="data.uri"
          />
        </div>
      </template>
    </order-modal-row-layout>
  </div>
</template>
<script>
import { TrawlRedIcon, SendIcon, ReceiveIcon } from "../../icons";
import packageAddress from "../../packages/package-address.vue";
import EnlargeableImage from "@diracleo/vue-enlargeable-image";
export default {
  components: {
    EnlargeableImage,
    packageAddress,
    TrawlRedIcon,
    SendIcon,
    ReceiveIcon,
  },
  props: {
    package: {
      type: Object,
      default: () => {},
    },
    price: {
      type: Object,
      default: () => null,
    },
  },
  data() {
    return {
      EnlargeableImage,
      URIImage,
    };
  },
  computed: {
    code() {
      return this.package?.code?.content;
    },
    created_at() {
      if (this.package?.attachments[0] == null) {
        this.URIImage = null;
      } else {
        this.URIImage = this.package?.attachments;
      }
      return this.package?.created_at;
    },
    imagePacking() {
      return this.package?.attachments[0];
    },
  },
};
</script>
