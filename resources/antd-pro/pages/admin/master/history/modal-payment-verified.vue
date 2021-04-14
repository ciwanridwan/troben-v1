<template>
  <div>
    <a-button :class="['trawl-button-success']" @click="showModal"
      >Konfirmasi Pembayaran</a-button
    >
    <a-modal :visible="visible" @cancel="onCancel">
      <template slot="closeIcon">
        <a-icon type="close" @click="onCancel"></a-icon>
      </template>
      <a-carousel>
        <a-row
          v-for="(item, index) in record.attachments"
          :key="index"
          type="flex"
        >
          <a-col :span="24">
            <img :src="item.uri" :alt="item.title" />
          </a-col>
        </a-row>
      </a-carousel>
      <template slot="footer">
        <a-button block :class="['trawl-button-success']" @click="onOk"
          >Konfirmasi Pembayaran</a-button
        >
      </template>
    </a-modal>
  </div>
</template>
<script>
export default {
  props: {
    record: {
      type: Object,
      default: () => {}
    },
    paymentVerfied: {
      type: Function,
      default: () => {}
    }
  },
  data() {
    return {
      visible: false
    };
  },
  methods: {
    showModal() {
      this.visible = true;
    },
    onOk() {
      this.paymentVerfied(this.record.hash);
      this.onCancel();
    },
    onCancel() {
      this.visible = false;
    }
  }
};
</script>
