<template>
  <a-layout id="content-layout">
    <a-layout-sider v-if="hasSiderSlot && siderPosition == 'left'">
      <slot name="sider"></slot>
    </a-layout-sider>
    <a-layout-content class="content">
      <a-row class="content-layout-head">
        <a-col :span="10">
          <slot name="title" v-if="hasTitleSlot"></slot>
          <h3 v-else>{{ title ? title : defaultTitle }}</h3>
        </a-col>
        <a-col :span="14">
          <a-row type="flex">
            <a-col :span="search ? 16 : 24">
              <slot name="head-tools"></slot>
            </a-col>
            <a-col v-if="search" :span="8">
              <a-input-search
                @search="search.action"
                :placeholder="
                  search.placeholder ? search.placeholder : 'search ...'
                "
              ></a-input-search>
            </a-col>
          </a-row>
        </a-col>
      </a-row>
      <a-layout>
        <a-layout-content>
          <slot name="content"></slot>
        </a-layout-content>
        <slot name="footer" v-if="hasFooterSlot"></slot>
      </a-layout>
    </a-layout-content>
    <a-layout-sider
      v-if="hasSiderSlot && siderPosition == 'right'"
      class="content-layout-sider"
    >
      <slot name="sider"></slot>
    </a-layout-sider>
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
    search: {
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
    },
    hasFooterSlot() {
      return !!this.$slots["footer"];
    }
  },
  methods: {
    getNavigation
  }
};
</script>
<style lang="scss">
#content-layout {
  height: 90vh;
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
