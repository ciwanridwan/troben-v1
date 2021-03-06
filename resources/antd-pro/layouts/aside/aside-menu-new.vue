<template>
  <a-layout-sider :collapsed="true">
    <main-menu-new></main-menu-new>
  </a-layout-sider>
</template>
<script>
import MainMenuDetail from "./main-menu-detail.vue";
import mainMenu from "./main-menu.vue";
export default {
  components: { mainMenu, MainMenuDetail },
};
</script>
