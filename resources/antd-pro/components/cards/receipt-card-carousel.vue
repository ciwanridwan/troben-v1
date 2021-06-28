<template>
  <a-carousel
    ref="carousel"
    :infinite="false"
    :swipeToSlide="true"
    :slidesToShow="1"
    class="receipt-card-carousel"
  >
    <div slot="prevArrow">
      <a-icon type="left-circle" />
    </div>
    <template v-for="(item, index) in items">
      <receipt-card
        ref="receiptCard"
        v-for="(code, codeIndex) in item.codes"
        :key="`${index}-${codeIndex}`"
        :package="package"
        :item="item"
        :code="code"
      ></receipt-card>
    </template>
  </a-carousel>
</template>
<script>
import receiptCard from "./receipt-card.vue";
export default {
  components: { receiptCard },
  props: {
    package: {
      type: Object,
      default: () => {}
    }
  },
  computed: {
    items() {
      return this.package?.items;
    }
  }
};
</script>
