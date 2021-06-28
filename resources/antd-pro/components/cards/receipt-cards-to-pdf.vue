<template>
  <div>
    <template v-for="(item, index) in items">
      <template v-for="(code, codeIndex) in item.codes">
        <section class="pdf-item" :key="`${index}-${codeIndex}`">
          <receipt-card
            ref="receiptCard"
            :package="package"
            :item="item"
            :code="code"
          ></receipt-card>
        </section>
        <div
          class="html2pdf__page-break"
          :key="`page-break-${index}-${codeIndex}`"
        />
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
