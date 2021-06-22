<template>
  <a-modal :visible="visible">
    <order-modal-row-layout>
      <template slot="icon">
        <a-icon :component="TrawlRedIcon" />
      </template>
      <template slot="content">
        <a-space direction="vertical">
          <span class="trawl-text-bolder"> RCPXXX </span>
          <span class="">06/05/04 21:21:12</span>
        </a-space>
      </template>
    </order-modal-row-layout>
    <order-modal-row-layout>
      <template slot="icon">
        <a-icon :component="TrawlRedIcon" />
      </template>
      <template slot="content">
        <a-space direction="vertical">
          <span class="trawl-text-bolder"> RCPXXX </span>
          <span class="">06/05/04 21:21:12</span>
        </a-space>
      </template>
    </order-modal-row-layout>
  </a-modal>
</template>
<script>
import { TrawlRedIcon } from "../icons";
import orderModalRowLayout from "../orders/order-modal-row-layout.vue";
export default {
  components: { orderModalRowLayout },
  data() {
    return {
      TrawlRedIcon,
      visible: false,
    };
  },
};
</script>
