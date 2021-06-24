<template>
  <a-row type="flex" justify="space-between">
    <a-col :span="11">
      <a-space direction="vertical">
        <a-space>
          <a-icon :component="SendIcon" :style="{ 'font-size': '1.5rem' }" />
          <h3>Pengirim</h3>
        </a-space>
        <package-address :package="package" type="sender" :title="false" />
        <receipt-card-item-qr-code :code="code" />
      </a-space>
    </a-col>
    <a-col :span="11">
      <a-space direction="vertical">
        <a-space>
          <a-icon :component="ReceiveIcon" :style="{ 'font-size': '1.5rem' }" />
          <h3>Penerima</h3>
        </a-space>
        <package-address :package="package" type="receiver" :title="false" />
        <package-item-detail :item="item" direction="vertical" />
        <span class="trawl-text-mute">www.trawlbens.id</span>
      </a-space>
    </a-col>
  </a-row>
</template>
<script>
import { SendIcon, ReceiveIcon } from "../icons";
import PackageAddress from "../packages/package-address.vue";
import PackageItemDetail from "../packages/package-item-detail.vue";
import ReceiptCardItemQrCode from "./receipt-card-item-qr-code.vue";
export default {
  props: ["package", "item", "code"],
  components: {
    PackageAddress,
    ReceiptCardItemQrCode,
    PackageItemDetail,
  },
  data() {
    return {
      SendIcon,
      ReceiveIcon,
    };
  },
};
</script>
