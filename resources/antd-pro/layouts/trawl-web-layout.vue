<template>
  <a-layout :class="['admin-layout', 'beauty-scroll']">
    <a-layout-sider :collapsed="true">
      <trawl-main-menu :navigation="navigation"></trawl-main-menu>
    </a-layout-sider>
    <a-layout class="admin-layout-main beauty-scroll">
      <trawl-header></trawl-header>
      <a-layout-content
        class="admin-layout-content"
        :style="`min-height: ${minHeight}px;`"
      >
        <a-layout style="position: relative">
          <trawl-main-menu-detail
            v-if="this.currentRouteHasChildren()"
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
import TrawlMainMenuDetail from "./aside/trawl-main-menu-detail.vue";
import TrawlMainMenu from "./aside/trawl-main-menu.vue";
import AdminHeaderNew from "./header/admin-header-new.vue";
import TrawlHeader from "./header/trawl-header.vue";

export default {
  components: {
    asideMenuNew,
    AdminHeaderNew,
    MainMenuDetail,
    MainMenuNew,
    TrawlMainMenu,
    TrawlHeader,
    TrawlMainMenuDetail
  },
  props: {
    sidebar: {
      type: Boolean,
      default: false
    },
    navigation: {
      type: Object,
      default: () => ({})
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
      return this.currentRouteHasChildren() ? "200px" : "0px";
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
    },
    currentRouteHasChildren() {
      let currentRoute = this.getRoute();
      let splitRoute = currentRoute.split('.');
      if (splitRoute[0] === 'admin') {
        return this.navigation[splitRoute[1]].children !== null;
      } else if (splitRoute[0] === 'partner' && splitRoute[1] === 'customer_service') {
        return this.navigation[splitRoute[2]].children !== null
      } else {
        return true;
      }
    },
  },
  created() {
    this.init();
    this.setMessaging();
    this.runServiceWorker();
    this.getNotification();
  },
};
</script>
