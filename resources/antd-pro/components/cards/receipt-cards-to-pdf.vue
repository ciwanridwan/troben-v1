<template>
  <div>
    <template v-for="(item, index) in items">
      <template v-for="(code, codeIndex) in item.codes">
        <receipt-card
          class="pdf-item"
          :key="`${index}-${codeIndex}`"
          ref="receiptCard"
          :package="package"
          :item="item"
          :code="code"
        ></receipt-card>
        <!-- <section>
        </section> -->
        <!-- <div
          class="html2pdf__page-break"
          :key="`page-break-${index}-${codeIndex}`"
        /> -->
      </template>
    </template>
  </div>
</template>
<script>
export default {
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
