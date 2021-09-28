const trawlNotification = {
  data() {
    return {
      filter: {
        per_page: -1
      },
    }
  },
  methods: {
    async getNotification(params = {}) {
      const { data } = await this.$http
        .get(this.routeUri(`${ this.getRouteBase() }notification`), {
          params: {
            type: status,
            ...this.filter,
            ...params
          }
        })
      this.$trawlNotificationData = data.data;
    },
    async readNotification(notification_key) {
      const { data } = await this.$http
        .patch(this.routeUri(`${ this.getRouteBase() }notification.read`,{
          notification_id: notification_key
        }))

      if (data.code === '0000') {
        await this.getNotification();
      }
    },
    getRouteBase() {
      return this.isAdmin()
        ? "admin."
        : "partner."
    },
  },
  computed: {

  }
}

export default trawlNotification;
