<template>
  <a-space direction="vertical" :size="1">
    <span
      >Total Charge Weight :
      <span class="trawl-text-bolder">{{ total_weight }}</span>
      Kg
    </span>
    <span
      >Total per Kg :
      <span class="trawl-text-bolder">{{ currency(tier_price) }}</span>
    </span>
    <span
    >Total Biaya :
      <span class="trawl-text-bolder">{{ currency(total_amount) }}</span>
    </span>
    <span
    >Status Pembayaran :
      <span class="trawl-text-bolder">{{ capitalizeFirstLetter(payment_status) }}</span>
    </span>
  </a-space>
</template>
<script>
export default {
  props: {
    package: {
      type: Object,
      default: () => {},
    },
  },
  computed: {
    total_weight() {
      return this.package?.total_weight;
    },
    tier_price() {
      return this.package?.tier_price;
    },
    total_amount() {
      return this.package?.total_amount;
    },
    payment_status() {
      return this.package?.payment_status;
    },
  },
};
</script>
