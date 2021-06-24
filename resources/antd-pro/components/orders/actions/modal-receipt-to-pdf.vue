<template>
  <modal-to-pdf :fileName="fileName">
    <template slot="trigger">
      <a-button type="success" class="trawl-button-success">Print</a-button>
    </template>
    <template slot="content">
      <receipt-card :package="package" />
    </template>
  </modal-to-pdf>
</template>
<script>
import modalToPdf from "../../modals/modal-to-pdf.vue";
export default {
  props: {
    package: {
      type: Object,
      default: () => {},
    },
  },
  computed: {
    fileName() {
      return `${this.package?.code?.content} ${this.dateSimpleFormat(new Date())}`;
    },
  },
  components: { modalToPdf },
};
</script>
