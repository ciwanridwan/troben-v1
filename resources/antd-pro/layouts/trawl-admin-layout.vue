<template>
  <trawl-web-layout ref="layout" :navigation="navigation">
    <template v-for="item in slotKeys" :slot="item">
      <slot :name="item"></slot>
    </template>
  </trawl-web-layout>
</template>
<script>
import { main as navigation } from "../navigation";
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
      console.log("slots", this.$slots);
      return Object.keys(this.$slots);
    }
  }
};
</script>
