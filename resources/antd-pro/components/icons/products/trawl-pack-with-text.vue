<template>
  <span>
    <a-space align="center">
      <a-icon
        :component="TrawlPackIcon"
        :style="{ 'font-size': '.9rem' }"
      ></a-icon>
      <span :style="{ 'font-size': '0.65rem' }">TRAWL <b>PACK</b></span>
    </a-space>
  </span>
</template>
<script>
import TrawlPackIcon from "./trawlPackIcon.vue";
export default {
  data() {
    return {
      TrawlPackIcon
    };
  }
};
</script>
