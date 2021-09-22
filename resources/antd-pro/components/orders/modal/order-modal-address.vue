<template>
  <div>
    <order-modal-row-layout>
      <template slot="icon">
        <a-icon :component="TrawlRedIcon" :style="{ 'font-size': '3rem' }" />
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
        <a-icon :component="SendIcon" :style="{ 'font-size': '3rem' }" />
      </template>
      <template slot="content">
        <package-address :package="package" type="sender" />
      </template>
    </order-modal-row-layout>

    <order-modal-row-layout :afterLine="false">
      <template slot="icon">
        <a-icon :component="ReceiveIcon" :style="{ 'font-size': '3rem' }" />
      </template>
      <template slot="content">
        <a-space direction="vertical" :size="1">
          <package-address :package="package" type="receiver" />
          <order-estimation v-if="this.price" :price="this.price"/>
        </a-space>
      </template>
      <template slot="addon">
        <a-empty v-if="package.attachments[0] == null" />
        <enlargeable-image v-else :src="URIImage" />
      </template>
    </order-modal-row-layout>
  </div>
</template>
<script>
import { TrawlRedIcon, SendIcon, ReceiveIcon } from "../../icons";
import packageAddress from "../../packages/package-address.vue";
import EnlargeableImage from '@diracleo/vue-enlargeable-image';
export default {
  components: {
    EnlargeableImage,
    packageAddress
  },
  props: {
    package: {
      type: Object,
      default: () => {},
    },
    price: {
      type: Object,
      default: () => null,
    }
  },
  data() {
    return {
      TrawlRedIcon,
      SendIcon,
      ReceiveIcon,
      EnlargeableImage,
      URIImage
    };
  },
  computed: {
    code() {
      return this.package?.code?.content;
    },
    created_at() {
      if (this.package?.attachments[0] == null){
        this.URIImage = null
      }else{
        this.URIImage = this.package?.attachments[0].uri
      }
      return this.package?.created_at;
    },
    imagePacking(){
      return this.package?.attachments[0]
    }
  },
};
</script>
