<template>
  <a-space direction="vertical" :size="1">
    <trawl-pack-with-text />
    <h2 class="trawl-text-bolder">
      {{ partnerType }}
    </h2>
    <p>
      {{ partnerAddress }}
    </p>
    <span class="trawl-text-mute trawl-text-bold">
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
    partnerType() {
      let partner = getPartnerByType(this.partner?.type);
      return partner?.title;
    },
  },
};
</script>
