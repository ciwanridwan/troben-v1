<template>
  <a-card class="receipt-card">
    <a-row type="flex" justify="space-between">
      <a-col :span="12">
        <receipt-card-partner-info />
      </a-col>
      <a-col :span="12" class="trawl-text-center">
        <a-icon
          class="receipt-card--icon"
          :component="TrawlLabelImage"
          :style="{ 'font-size': '4rem' }"
        ></a-icon>
      </a-col>
    </a-row>
    <a-row type="flex">
      <a-col :span="12">
        <receipt-card-package-info :package="package" />
      </a-col>
      <a-col :span="12" class="trawl-text-center">
        <receipt-card-package-qr-code :package="package" />
      </a-col>
    </a-row>
<!--    <a-divider :style="{ 'margin-top': '0.05rem', 'margin-bottom': '0.05rem'}" />-->
    <receipt-card-item-info :package="package" :item="item" :code="code" />
  </a-card>
</template>
<script>
import { TrawlLabelImage } from "../icons";
import ReceiptCardItemInfo from "./receipt-card-item-info.vue";
import ReceiptCardPackageInfo from "./receipt-card-package-info.vue";
import ReceiptCardPackageQrCode from "./receipt-card-package-qr-code.vue";
import receiptCardPartnerInfo from "./receipt-card-partner-info.vue";
export default {
  props: ["package", "item", "code"],
  components: {
    receiptCardPartnerInfo,
    ReceiptCardPackageInfo,
    ReceiptCardPackageQrCode,
    ReceiptCardItemInfo
  },
  data() {
    return {
      TrawlLabelImage
    };
  }
};
</script>
