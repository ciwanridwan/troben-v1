<script>
export default {
  props: {
    src: {
      required: true,
      type: String
    }
  },
  data: () => ({
    loading: false,
    show: false,
    items: {}
  }),
  created() {
    this.items = this.getDefaultPagination()
    this.getItems()
  },
  methods: {
    getItems() {
      this.loading = true
      this.$http.get(this.src)
        .then((response) => this.items = response.data)
        .catch((error) => this.handleXhrError(error))
        .finally(() => this.loading = false)
    }
  }
}
</script>
<template>
  <a-dropdown :trigger="['click']" v-model="show">
    <div slot="overlay">
      <a-spin :spinning="loading">
        <a-tabs class="dropdown-tabs" :tabBarStyle="{textAlign: 'center'}" :style="{width: '297px'}">
          <a-tab-pane tab="Unread" key="1">
            <a-list class="tab-pane">
              <a-list-item v-for="item in items.data" :key="item.id">

              </a-list-item>
            </a-list>
          </a-tab-pane>
        </a-tabs>
      </a-spin>
    </div>
    <span @click="getItems" class="header-notification">
            <a-badge class="notification-badge" :count="items.total">
                <a-icon :class="['header-notification-icon']" type="bell"/>
            </a-badge>
        </span>
  </a-dropdown>
</template>
