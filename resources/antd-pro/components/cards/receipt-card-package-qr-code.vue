<template>
  <a-space direction="vertical" :size="1">
    <vue-qrcode class="receipt-card--qr-code" :value="code"></vue-qrcode>
    <span class="trawl-text-bolder" :style="textStyle" > No Resi: {{ code }} </span>
  </a-space>
</template>
<script>
export default {
  data() {
    return {
      textStyle: { "font-size": ".55rem" },
    }
  },
  props: ["package"],
  computed: {
    code() {
      return this.package?.code?.content;
    },
  },
};
</script>
