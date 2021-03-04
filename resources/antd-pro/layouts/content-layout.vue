<template>
  <a-layout id="content-layout">
    <a-layout-sider v-if="hasSiderSlot && siderPosition == 'left'">
      <slot name="sider"></slot>
    </a-layout-sider>
    <a-layout-content>
      <a-row class="content-layout-head">
        <a-col :span="12">
          <slot name="title" v-if="hasTitleSlot"></slot>
          <h3 v-else>{{ title ? title : defaultTitle }}</h3>
        </a-col>
        <a-col :span="12">
          <slot name="head-tools"></slot>
        </a-col>
      </a-row>
      <slot name="content"></slot>
    </a-layout-content>
    <a-layout-sider v-if="hasSiderSlot && siderPosition == 'right'">
      <slot name="sider"></slot>
    </a-layout-sider>
    <!-- <a-layout-footer> </a-layout-footer> -->
  </a-layout>
</template>
<script>
import getNavigation from "../navigation/navigation";

export default {
  props: {
    title: {
      type: String
    },
    sider: {
      type: Boolean,
      default: false
    },
    siderPosition: {
      type: String,
      default: "left"
    },
    pagination: {
      type: Object,
      default: () => {}
    }
  },
  computed: {
    defaultTitle() {
      let route = this.getNavigation(this.getRoute());
      return route.title ? route.title : route.text;
    },
    hasTitleSlot() {
      return !!this.$slots["title"];
    },
    hasSiderSlot() {
      return !!this.$slots["sider"];
    }
  },
  methods: {
    getNavigation
  }
};
</script>
<style lang="scss">
#content-layout {
  .ant-layout-content {
    .content-layout {
      &-head {
        margin-bottom: 24px;
      }
    }
  }
}
</style>
