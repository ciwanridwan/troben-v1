<template>
  <a-layout-sider :theme="sideTheme" :class="['side-menu', 'beauty-scroll', isMobile ? null : 'shadow']" width="256px"
                  :collapsible="collapsible" v-model="collapsed" :trigger="null">
    <div :class="['logo', theme]">
      <img src="/assets/logo.png"/>
      <h1>{{ appName }}</h1>
    </div>

    <main-menu :theme="theme" :collapsed="collapsed"/>
  </a-layout-sider>
</template>

<script>
export default {
  props: {
    collapsible: {
      type: Boolean,
      required: false,
      default: false
    },
    collapsed: {
      type: Boolean,
      required: false,
      default: false
    },
    theme: {
      type: String,
      required: false,
      default: ''
    },
  },
  computed: {
    appName() {
      return this.config.app.name
    },
    isMobile() {
      return this.config.layout.is_mobile
    },
    sideTheme() {
      return this.theme === '' ? this.config.layout.aside.theme : this.theme;
    }
  },
}
</script>

