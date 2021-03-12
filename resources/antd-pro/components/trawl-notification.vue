<template>
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
      v-for="index in 10"
      :key="index"
      :class="[
        'ant-card-normal',
        'trawl-card-notification',
        'trawl-card-notification-item',
        index === 1 ? 'trawl-card-notification-item-first' : null
      ]"
    >
      <a-row type="flex" align="middle">
        <a-col :span="20">
          <h5 class="trawl-text-normal">
            <a-space>
              <calendar-icon></calendar-icon>
              {{ currentDate }}
            </a-space>
          </h5>
          <h4>
            <b>Bukti Pembayaran</b>
          </h4>
          <h5 class="trawl-text-mute">Customer telah mengirim bukti</h5>
        </a-col>
        <a-col :span="4" class="trawl-text-center">
          <a-icon type="arrow-right"></a-icon>
        </a-col>
      </a-row>
    </a-card>
  </div>
</template>
