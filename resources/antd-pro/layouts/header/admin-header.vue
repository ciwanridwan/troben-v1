<template>
  <a-layout-header :class="[headerTheme, 'admin-header']">
    <div :class="['admin-header-wide', layout, pageWidth]">
      <a-icon v-if="layout !== 'head'" class="trigger" :type="collapsed ? 'menu-unfold' : 'menu-fold'"
              @click="toggleCollapse"/>

      <a-button type="primary" icon="plus" :href="routeUri('app.patient.create')">
        Add New Patient Registry
      </a-button>

      <div :class="['admin-header-right', headerTheme]">
        <header-notification :src="routeUri('app.notification.unread')"/>
        <header-user class="header-item"/>
      </div>
    </div>
  </a-layout-header>
</template>
<script>
export default {
  props: {
    theme: {
      type: String,
      required: false,
      default: ''
    },
    collapsed: {
      type: Boolean,
      required: true
    }
  },
  computed: {
    pageWidth() {
      return this.config.layout.width
    },
    layout() {
      return this.config.layout.mode
    },
    headerTheme() {
      return this.theme === '' ? this.config.layout.header.theme : this.theme
    },
  },
  methods: {
    toggleCollapse() {
      this.$emit('toggleCollapse')
    },
  }
}
</script>
