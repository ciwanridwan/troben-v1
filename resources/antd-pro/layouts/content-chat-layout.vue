<template>
  <a-layout id="content-layout-chat">
    <a-layout-sider
      v-if="hasLeftSiderSlot"
      class="trawl-bg-white trawl-chat--list"
    >
      <slot name="sider-left"></slot>
    </a-layout-sider>
    <a-layout-content
      id="scrollingContainer"
      class="content"
      style="overflow: scroll; margin-bottom: 5vh"
    >
      <a-row class="content-layout-head trawl-bg-white trawl-chat-header">
        <slot name="content-head"></slot>
      </a-row>
      <a-layout>
        <a-layout-content>
          <slot name="content"></slot>
        </a-layout-content>
        <slot name="footer" v-if="hasFooterSlot"></slot>
      </a-layout>
    </a-layout-content>
    <a-layout-sider v-if="hasRightSiderSlot" class="content-layout-sider">
      <slot name="sider-right"></slot>
    </a-layout-sider>
  </a-layout>
</template>
<script>
import { getNavigation } from "../navigation";

export default {
  props: {
    title: {
      type: String,
    },
    sider: {
      type: Boolean,
      default: false,
    },
    siderPosition: {
      type: String,
      default: "left",
    },
    search: {
      type: Object,
      default: () => {},
    },
  },
  computed: {
    defaultTitle() {
      let route = this.getNavigation(this.getRoute());
      return route.title ? route.title : route.text;
    },
    hasTitleSlot() {
      return !!this.$slots["title"];
    },
    hasSubTitleSlot() {
      return !!this.$slots["subTitle"];
    },
    hasLeftSiderSlot() {
      return !!this.$slots["sider-left"];
    },
    hasRightSiderSlot() {
      return !!this.$slots["sider-right"];
    },
    hasFooterSlot() {
      return !!this.$slots["footer"];
    },
  },
  methods: {
    getNavigation,
  },
};
</script>
<style lang="scss">
#content-layout {
  min-height: 90vh;
  position: relative;
  .content {
    padding: 24px;
  }
  .ant-layout-content {
    .content-layout {
      &-head {
        margin-bottom: 24px;
      }
    }
  }
}
</style>
