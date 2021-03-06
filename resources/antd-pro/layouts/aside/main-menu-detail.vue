<template>
  <a-layout-sider
    class="trawl-main-menu-detail"
    :style="{ overflow: 'auto', height: '100vh', position: 'fixed', top: 0 }"
  >
    <div class="trawl-main-menu-detail-content">
      <h3>
        <a-icon></a-icon>
        {{ navigation.title ? navigation.title : navigation.text }}
      </h3>
      <a-menu
        mode="inline"
        class="menu"
        :defaultSelectedKeys="activeKeys"
        :defaultOpenKeys="openedKeys"
      >
        <sub-menu
          v-for="item in navigation.children"
          :key="item.route"
          :menuInfo="item"
        ></sub-menu>
      </a-menu>
    </div>
  </a-layout-sider>
</template>
<script>
import { getNavigation } from "../../navigation";
import subMenu from "./sub-menu.vue";

export default {
  components: { subMenu },
  methods: {
    getNavigation,
  },
  computed: {
    openedKeys() {
      if (this.collapsed) return [];

      return this.activeKeys;
    },
    activeKeys() {
      let opened = [];
      let route = this.getRoute();

      // opened.push(this.getNavigation(route, this.navigation).route);

      return opened;
    },
  },
  data() {
    return {
      navigation: {},
    };
  },
  created() {
    this.navigation = this.getNavigation(this.getRoute());
  },
};
</script>
