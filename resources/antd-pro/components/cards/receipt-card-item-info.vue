<template>
  <a-row type="flex" justify="space-between">
    <a-col :span="11">
      <a-space direction="vertical" size="small">
        <a-space>
          <a-icon :component="SendIcon" :style="textStyle" />
          <h3 :style="textStyle">Pengirim</h3>
        </a-space>
        <package-address :package="package" type="sender" :title="false" />
        <receipt-card-item-qr-code :code="code" />
      </a-space>
    </a-col>
    <a-col :span="11">
      <a-space direction="vertical" size="small">
        <a-space>
          <a-icon :component="ReceiveIcon" :style="textStyle" />
          <h3 :style="textStyle">Penerima</h3>
        </a-space>
        <package-address :package="package" type="receiver" :title="false" />
        <package-item-detail :item="item" direction="vertical" />
        <span class="trawl-text-mute" :style="{ 'font-size': '.5rem' }">www.trawlbens.id</span>
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
      textStyle: { "font-size": ".55rem" },
    };
  },
};
</script>
