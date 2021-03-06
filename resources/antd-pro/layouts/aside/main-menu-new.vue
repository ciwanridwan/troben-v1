<template>
  <a-menu
    mode="inline"
    class="menu"
    :defaultSelectedKeys="activeKeys"
    :defaultOpenKeys="openedKeys"
    :inline-collapsed="true"
    id="trawl-main-menu"
    ref="trawlMainMenu"
  >
    <a-menu-item v-for="item in mainNavigation" :key="item.route">
      <a :href="routeUri(item.route)">
        <a-icon :type="item.icon" />
        <span>{{ item.text }}</span>
      </a>
    </a-menu-item>
  </a-menu>
</template>

<script>
import { main as navigation, getNavigation } from "../../navigation";
import subMenu from "./sub-menu.vue";

export default {
  components: { subMenu },
  computed: {
    openedKeys() {
      if (this.collapsed) return [];

      return this.activeKeys;
    },
    activeKeys() {
      let opened = [];
      let route = this.getRoute();

      opened.push(this.getNavigation(route).route);

      return opened;
    },
    mainNavigation() {
      let main = { ...this.navigation };
      _.forEach(main, (o, k) => {
        if (_.isObject(o.children)) {
          main[k].route = o.children[_.head(Object.keys(o.children))].route;
          main[k].uri = o.children[_.head(Object.keys(o.children))].uri;
        }
      });
      return main;
    }
  },
  data: () => ({
    navigation
  }),
  methods: {
    navigate(route) {
      window.location.href = this.routeUri(route);
    },
    getNavigation
  }
};
</script>
