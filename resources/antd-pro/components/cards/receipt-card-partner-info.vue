<template>
  <a-space direction="vertical" :size=".1">
    <trawl-pack-with-text />
    <h2 class="trawl-text-bolder" :style="{ 'font-size': '.65rem' }">
      {{ partnerType }}
    </h2>
    <p class="trawl-text-bolder" :style="{ 'font-size': '.55rem' }">
      {{ partnerCode }}
    </p>
    <span class="trawl-text-mute trawl-text-bold" :style="{ 'font-size': '.55rem' }">
      {{ dateSimpleFormat(new Date()) }}
    </span>
  </a-space>
</template>
<script>
import { getPartnerByType } from "../../functions/partnerType";
import trawlPackWithText from "../icons/products/trawl-pack-with-text.vue";
export default {
  components: { trawlPackWithText },
  computed: {
    partner() {
      return this.$laravel.user.partners[0];
    },
    partnerAddress() {
      return this.partner?.address;
    },
    partnerCode() {
      return this.partner?.code;
    },
    partnerType() {
      let partner = getPartnerByType(this.partner?.type);
      return partner?.title;
    },
  },
};
</script>
