<template>
  <a-layout-sider
    class="trawl-main-menu-detail"
    :style="{ overflow: 'auto', height: '100vh', position: 'fixed', top: 0 }"
  >
    <div class="trawl-main-menu-detail-content">
      <h3 class="trawl-main-menu-detail-title">
        {{ title }}
      </h3>

      <a-menu
        mode="inline"
        class="menu"
        :defaultSelectedKeys="activeKeys"
        :defaultOpenKeys="openedKeys"
      >
        <trawl-sub-menu
          v-for="item in subMenu"
          :key="item.route"
          :menuInfo="item"
        ></trawl-sub-menu>
      </a-menu>
    </div>
  </a-layout-sider>
</template>
<script>
import { getNavigation, getParent } from "../../navigation";
import subMenu from "./sub-menu.vue";
import TrawlSubMenu from "./trawl-sub-menu.vue";

export default {
  props: {
    navigation: {
      type: Object,
      default: () => ({})
    }
  },
  components: { subMenu, TrawlSubMenu },
  methods: {
    getNavigation,
    getParent
  },
  computed: {
    openedKeys() {
      if (this.collapsed) return [];

      return this.activeKeys;
    },
    activeKeys() {
      let opened = [];
      let route = this.getRoute();

      opened.push(this.getNavigation(route, this.subMenu).route);

      return opened;
    },
    menu() {
      let menu = this.getParent(this.getRoute(), this.navigation);
      if (menu == null) {
        menu = this.navigation[Object.keys(this.navigation)[0]];
      }

      return menu;
    },
    subMenu() {
      return this.transUri(this.menu?.children);
    },
    title() {
      return this.menu?.title ? this.menu?.title : this.menu?.text ?? "";
    }
  }
};
</script>
