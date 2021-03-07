<template>
  <a-layout-sider
    class="trawl-main-menu-detail"
    :style="{ overflow: 'auto', height: '100vh', position: 'fixed', top: 0 }"
  >
    <div class="trawl-main-menu-detail-content">
      <h3 class="trawl-main-menu-detail-title">
        {{ menu.title ? menu.title : menu.text }}
      </h3>
      <a-menu
        mode="inline"
        class="menu"
        :defaultSelectedKeys="activeKeys"
        :defaultOpenKeys="openedKeys"
      >
        <sub-menu
          v-for="item in subMenu"
          :key="item.route"
          :menuInfo="item"
        ></sub-menu>
      </a-menu>
    </div>
  </a-layout-sider>
</template>
<script>
import { main as navigation, getNavigation } from "../../navigation";
import subMenu from "./sub-menu.vue";

export default {
  components: { subMenu },
  methods: {
    getNavigation
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
      let menu = null;
      _.forEach(this.navigation, (o, k) => {
        if (this.getRoute().indexOf(o.route) >= 0) {
          menu = o;
        }
      });
      return menu;
    },
    subMenu() {
      return this.transUri(this.menu.children);
    }
  },
  data() {
    return {
      navigation
    };
  },
  created() {}
};
</script>
