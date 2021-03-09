<template>
  <trawl-web-layout ref="layout" :navigation="navigation">
    <template v-for="item in slotKeys" :slot="item">
      <slot :name="item"></slot>
    </template>
  </trawl-web-layout>
</template>
<script>
import navigation from "../navigation/partner/customer-service/main";
import trawlWebLayout from "./trawl-web-layout.vue";

export default {
  components: { trawlWebLayout },
  data() {
    return {
      navigation
    };
  },
  computed: {
    slotKeys() {
      return Object.keys(this.$slots);
    }
  }
};
</script>
