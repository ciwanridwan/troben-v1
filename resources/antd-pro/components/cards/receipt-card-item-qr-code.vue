<template>
  <a-space direction="vertical" :size="1">
    <vue-qrcode
      v-if="codeContent"
      class="receipt-card-item--qr-code"
      :value="codeContent"
    ></vue-qrcode>
    <span :style="{ 'font-size': '.55rem' }" >No Barang: {{ codeContent }}</span>
  </a-space>
</template>
<script>
export default {
  props: ["code"],
  computed: {
    codeContent() {
      return this.code?.content;
    }
  }
};
</script>
