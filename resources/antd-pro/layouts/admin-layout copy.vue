<template>
  <a-layout :class="['admin-layout', 'beauty-scroll']">
    <pro-drawer v-if="isMobile" v-model="drawerOpen">
      <aside-menu :theme="theme" :collapsed="false" :collapsible="false" />
    </pro-drawer>
    <aside-menu
      :class="[fixedSideBar ? 'fixed-side' : '']"
      :theme="theme"
      v-else-if="layout === 'side' || layout === 'mix'"
      :collapsed="collapsed"
      :collapsible="true"
    />
    <div
      v-if="fixedSideBar && !isMobile"
      :style="
        `width: ${sideMenuWidth}; min-width: ${sideMenuWidth};max-width: ${sideMenuWidth};`
      "
      class="virtual-side"
    ></div>

    <a-layout class="admin-layout-main beauty-scroll">
      <admin-header
        :class="[{ 'fixed-header': fixedHeader }]"
        :style="headerStyle"
        :collapsed="collapsed"
        @toggleCollapse="toggleCollapse"
      />
      <a-layout-header
        :class="['virtual-header', { 'fixed-header': fixedHeader }]"
        v-show="fixedHeader"
      ></a-layout-header>
      <a-layout-content
        class="admin-layout-content"
        :style="`min-height: ${minHeight}px;`"
      >
        <a-layout style="position: relative">
          <slot name="content"></slot>
        </a-layout>
      </a-layout-content>

      <a-layout-footer v-show="!sidebar" style="padding: 0px">
        <slot name="footer"></slot>
      </a-layout-footer>
    </a-layout>
  </a-layout>
</template>

<script>
export default {
  props: {
    sidebar: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    fixedHeader() {
      return this.config.layout.header.fixed;
    },
    fixedSideBar() {
      return this.config.layout.aside.fixed;
    },
    sideMenuWidth() {
      return this.collapsed ? "80px" : "256px";
    },
    theme() {
      return this.config.layout.theme;
    },
    isMobile() {
      return this.config.layout.is_mobile;
    },
    layout() {
      return this.config.layout.mode;
    },
    headerStyle() {
      let width =
        this.fixedHeader && this.layout !== "head" && !this.isMobile
          ? `calc(100% - ${this.sideMenuWidth})`
          : "100%";
      let position = this.fixedHeader ? "fixed" : "static";
      return `width: ${width}; position: ${position};`;
    },
    collapsed() {
      return this.config.layout.aside.collapse;
    },
    minHeight() {
      return this.sidebar
        ? window.innerHeight - 64
        : window.innerHeight - 64 - 122;
    }
  },
  data: () => ({
    drawerOpen: false
  }),
  methods: {
    toggleCollapse() {
      this.config.layout.toggleCollapse();
    }
  }
};
</script>
