<template>
  <a-layout>
    <a-layout-sider v-if="sider && siderPosition == 'left'">
      <slot name="sider"></slot>
    </a-layout-sider>
    <a-layout-content>
      <a-row>
        <a-col :span="12">
          <h3>{{ title ? title : defaultTitle }}</h3>
        </a-col>
        <a-col :span="12">
          <slot name="head-tools"></slot>
        </a-col>
      </a-row>
      <slot name="content"></slot>
    </a-layout-content>
    <a-layout-sider v-if="sider && siderPosition == 'right'">
      <slot name="sider"></slot>
    </a-layout-sider>
    <a-layout-footer>
      <a-pagination></a-pagination>
    </a-layout-footer>
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
    }
  },
  methods: {
    getNavigation
  }
};
</script>
