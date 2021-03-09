<template>
  <a-layout :class="['admin-layout', 'beauty-scroll']">
    <aside-menu-new></aside-menu-new>
    <a-layout class="admin-layout-main beauty-scroll">
      <admin-header-new></admin-header-new>
      <a-layout-content
        class="admin-layout-content"
        :style="`min-height: ${minHeight}px;`"
      >
        <a-layout style="position: relative">
          <main-menu-detail
            ref="mainMenuDetail"
            :style="{ width: sideMenuWidth }"
          ></main-menu-detail>
          <a-layout-content :style="{ 'padding-left': sideMenuWidth }">
            <slot name="content"></slot>
          </a-layout-content>
        </a-layout>
      </a-layout-content>

      <!-- <a-layout-footer v-show="!sidebar" style="padding: 0px">
        <slot name="footer"></slot>
      </a-layout-footer> -->
    </a-layout>
  </a-layout>
</template>

<script>
import asideMenuNew from "./aside/aside-menu-new.vue";
import MainMenuDetail from "./aside/main-menu-detail.vue";
import AdminHeaderNew from "./header/admin-header-new.vue";
export default {
  components: { asideMenuNew, AdminHeaderNew, MainMenuDetail },
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
      return "200px";
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
