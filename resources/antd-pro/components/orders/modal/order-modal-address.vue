<template>
  <div>
    <order-modal-row-layout>
      <template slot="icon">
        <a-icon :component="TrawlRedIcon" :style="{ 'font-size': '3rem' }" />
      </template>
      <template slot="content">
        <a-space direction="vertical">
          <span class="trawl-text-bolder"> {{ code }} </span>
          <span>{{ created_at }}</span>
        </a-space>
      </template>
    </order-modal-row-layout>

    <order-modal-row-layout>
      <template slot="icon">
        <a-icon :component="SendIcon" :style="{ 'font-size': '3rem' }" />
      </template>
      <template slot="content">
        <a-space direction="vertical" :size="1">
          <span>Pengirim</span>
          <span class="trawl-text-bolder">{{ sender_name }}</span>
          <span>{{ sender_phone }}</span>
          <p class="trawl-text-normal">{{ sender_address }}</p>
        </a-space>
      </template>
    </order-modal-row-layout>

    <order-modal-row-layout :afterLine="false">
      <template slot="icon">
        <a-icon :component="ReceiveIcon" :style="{ 'font-size': '3rem' }" />
      </template>
      <template slot="content">
        <a-space direction="vertical" :size="1">
          <span>Penerima</span>
          <span class="trawl-text-bolder">{{ receiver_name }}</span>
          <span>{{ receiver_phone }}</span>
          <p>{{ receiver_address }}</p>
          <order-estimation />
        </a-space>
      </template>
      <template slot="addon">
        <a-empty :description="null" />
      </template>
    </order-modal-row-layout>
  </div>
</template>
<script>
import { getDestinationAddress, getOriginAddress } from "../../../functions/orders";
import { TrawlRedIcon, SendIcon, ReceiveIcon } from "../../icons";
export default {
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
    created_at() {
      return this.package?.created_at;
    },
  },
};
</script>
