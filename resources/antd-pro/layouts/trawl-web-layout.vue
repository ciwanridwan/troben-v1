<template>
  <a-layout :class="['admin-layout', 'beauty-scroll']">
    <a-layout-sider :collapsed="true">
      <trawl-main-menu :navigation="navigation"></trawl-main-menu>
    </a-layout-sider>
    <a-layout class="admin-layout-main beauty-scroll">
      <admin-header-new></admin-header-new>
      <a-layout-content
        class="admin-layout-content"
        :style="`min-height: ${minHeight}px;`"
      >
        <a-layout style="position: relative">
          <trawl-main-menu-detail
            ref="mainMenuDetail"
            :style="{ width: sideMenuWidth }"
            :navigation="navigation"
          ></trawl-main-menu-detail>
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
import MainMenuNew from "./aside/main-menu-new.vue";
import TrawlMainMenu from "./aside/trawl-main-menu.vue";
import AdminHeaderNew from "./header/admin-header-new.vue";
export default {
  components: {
    asideMenuNew,
    AdminHeaderNew,
    MainMenuDetail,
    MainMenuNew,
    TrawlMainMenu
  },
  props: {
    sidebar: {
      type: Boolean,
      default: false
    },
    navigation: {
      type: Object,
      default: () => {}
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
    drawerOpen: false,
    navigation
  }),
  methods: {
    toggleCollapse() {
      this.config.layout.toggleCollapse();
    }
  }
};
</script>
