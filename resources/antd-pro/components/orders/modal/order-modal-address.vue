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
          <order-estimation v-if="this.package.charge_price_note.notes" :note="this.package.charge_price_note"/>
        </a-space>
      </template>
      <template slot="addon">
        <a-empty :description="null" />
      </template>
    </order-modal-row-layout>
  </div>
</template>
<script>
import { TrawlRedIcon, SendIcon, ReceiveIcon } from "../../icons";
import packageAddress from "../../packages/package-address.vue";
export default {
  components: { packageAddress },
  props: {
    package: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      TrawlRedIcon,
      SendIcon,
      ReceiveIcon,
    };
  },
  computed: {
    code() {
      return this.package?.code?.content;
    },
    created_at() {
      return this.package?.created_at;
    },
  },
};
</script>
