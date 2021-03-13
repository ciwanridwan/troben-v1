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
    <a-menu-item v-for="item in navigation" :key="item.route">
      <a v-if="item.children === null" :href="routeUri(item.route)">
        <a-icon :type="item.icon" />
        <span>{{ item.text }}</span>
      </a>
      <a
        v-else
        :href="routeUri(item.children[getFirstChild(item.children)].route)"
      >
        <a-icon :type="item.icon" />
        <span>{{ item.text }}</span>
      </a>
    </a-menu-item>
  </a-menu>
</template>

<script>
import { getNavigation } from "../../navigation";
import subMenu from "./sub-menu.vue";

export default {
  props: {
    navigation: {
      type: Object,
      default: () => {}
    }
  },
  components: { subMenu },
  computed: {
    openedKeys() {
      if (this.collapsed) return [];

      return this.activeKeys;
    },
    activeKeys() {
      let opened = [];
      let route = this.getRoute();

      _.forEach(this.navigation, (o, k) => {
        if (route.indexOf(o.route) >= 0) {
          opened.push(o.route);
          return opened;
        }
      });

      return opened;
    }
  },
  methods: {
    navigate(route) {
      window.location.href = this.routeUri(route);
    },
    getNavigation,
    getFirstChild(children) {
      return _.head(Object.keys(children));
    }
  }
};
</script>
