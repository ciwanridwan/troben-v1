<!-- <template>
  <div>
    <a-card
      :class="[
        'ant-card-normal',
        'trawl-card-notification',
        'trawl-card-notification-header'
      ]"
    >
      <b>Notifikasi</b>
    </a-card>
    <a-card
      v-for="(notification,index) in data"
      :key="index"
      :class="[
        'ant-card-normal',
        'trawl-card-notification',
        'trawl-card-notification-item',
        index === 0 ? 'trawl-card-notification-item-first' : null
      ]"
    >
      <a-row type="flex" align="middle">
        <a-col :span="20">
          <h5 class="trawl-text-normal">
            <a-space>
              <calendar-icon></calendar-icon>
              {{ notification.notify_at }}
            </a-space>
          </h5>
          <h4>
            <b>{{ notification.title }}</b>
          </h4>
          <h5 class="trawl-text-mute">{{ notification.body }}</h5>
        </a-col>
        <a-col :span="4" class="trawl-text-center">
          <a-icon type="issues-close" title="close issue" @click="readNotification(notification.key)"></a-icon>
        </a-col>
      </a-row>
    </a-card>
  </div>
</template>

<script>
export default {
  data() {
    return {
      data: [],
    }
  },
  watch: {
    $trawlNotificationData(value) {
      this.data = value;
    }
  }
}
</script> -->
