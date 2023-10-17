<template>
  <a-popconfirm @confirm="confirm" ok-text="Kunjungi Halaman Admin">
    <template #title>
      Untuk saat ini silahkan gunakan Admin versi Terbaru.
    </template>
    <a-button type="success" class="trawl-button-success">
      <span>Print</span>
    </a-button>
  </a-popconfirm>
  <!-- <modal-to-pdf
    v-show="loaded"
    :fileName="fileName"
    :options="options"
    :package="package"
    :items="items"
  >
    <template slot="trigger">
      <a-button type="success" class="trawl-button-success">Print</a-button>
    </template>
    <template slot="content">
      <receipt-card-carousel :package="package" />
    </template>
    <template slot="pdf-content" :style="{ height: '150mm' }">
      <template v-for="(item, index) in items">
        <template v-for="(code, codeIndex) in item.codes">
          <section class="pdf-item" :key="`${index}-${codeIndex}`">
            <receipt-card
              ref="receiptCard"
              :package="package"
              :item="item"
              :code="code"
              :style="{
                height: '155mm',
                'margin-top': '0',
                'padding-bottom': '0',
              }"
            ></receipt-card>
          </section>
          <div
            class="html2pdf__page-break"
            :key="`page-break-${index}-${codeIndex}`"
          />
        </template>
      </template>
    </template>
  </modal-to-pdf> -->
</template>
<script>
import ReceiptCardCarousel from "../../cards/receipt-card-carousel.vue";
import ReceiptCard from "../../cards/receipt-card.vue";
import ReceiptCardsToPdf from "../../cards/receipt-cards-to-pdf.vue";
import modalToPdf from "../../modals/modal-to-pdf.vue";
export default {
  props: {
    package: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      options: {
        html2canvas: { scale: 3 },
        jsPDF: {
          orientation: "portrait",
          unit: "mm",
          format: [100, 165],
        },
        pagebreak: {
          mode: "legacy",
        },
      },
      loaded: false,
    };
  },
  methods: {
    confirm() {
      window.open("https://admin.trawlbens.com/", "_blank");
    },
  },
  computed: {
    fileName() {
      return `${this.package?.code?.content} ${this.dateSimpleFormat(
        new Date()
      )}`;
    },
    items() {
      return this.package?.items;
    },
  },
  components: {
    modalToPdf,
    ReceiptCardCarousel,
    ReceiptCardsToPdf,
    ReceiptCard,
  },
  mounted() {
    this.$nextTick(() => {
      this.loaded = true;
    });
  },
};
</script>
