<template>
  <div>
    <loading-modal v-model="loading" />
    <vue-html2pdf
      :filename="fileName"
      :enable-download="false"
      :show-layout="false"
      :manual-pagination="false"
      :paginate-elements-by-height="contentHeight"
      ref="html2Pdf"
      pdf-content-width="100%"
      @progress="onProgress($event)"
      @startPagination="startPagination()"
      @hasPaginated="hasPaginated()"
      @beforeDownload="beforeDownload($event)"
      @hasDownloaded="hasDownloaded($event)"
    >
      <section slot="pdf-content">
        <!-- PDF Content Here -->
        <slot name="pdf-content"></slot>
      </section>
    </vue-html2pdf>
    <span class="trawl-click" @click="showModal">
      <slot name="trigger"> </slot>
    </span>
    <a-modal :visible="visible" centered :width="width" :footer="footer">
      <template slot="closeIcon">
        <a-icon type="close" @click="hideModal" />
      </template>
      <template slot="title">
        <a-row type="flex" justify="space-between">
          <a-col :span="12">
            <slot name="title"></slot>
          </a-col>
          <a-col :span="5">
            <span v-if="domRendered" class="trawl-click" @click="saveToPdf">
              <slot v-if="hasSlot('printTrigger')" name="printTrigger"></slot>
              <a-icon
                v-else
                type="printer"
                :style="{ 'font-size': '1.5rem' }"
              ></a-icon>
            </span>
            <a-icon v-else type="loading" />
          </a-col>
        </a-row>
      </template>
      <slot name="content"></slot>
      <template slot="footer">
        <slot name="footer"></slot>
      </template>
    </a-modal>
  </div>
</template>
<script>
import VueHtml2pdf from "vue-html2pdf";
import JsPdf from "jspdf";
import svgAsPng from "save-svg-as-png";
import QRCode from "qrcode";
import LoadingModal from "../orders/modal/loading-modal.vue";
export default {
  components: {
    VueHtml2pdf,
    LoadingModal
  },
  props: {
    value: {
      type: Boolean,
      default: false
    },
    fileName: {
      type: String,
      default: ""
    },
    width: {
      default: "50%"
    },
    options: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      visible: false,
      contentHeight: 625,
      domRendered: false,
      footer: undefined,
      align: "center",
      loading: false
    };
  },
  computed: {
    content() {
      return this.$slots?.content ? this.$slots.content[0] : null;
    }
  },
  methods: {
    showModal() {
      this.visible = true;
    },
    hideModal() {
      this.visible = false;
    },
    async saveToPdf() {
      this.loading = true;
      // this.$refs.html2Pdf.generatePdf();

      //get uri png converted from svg asset
      const trawlLogoUri = await svgAsPng.svgAsPngUri(document.getElementById('trawllabelimage'));
      const trawlPackUri = await svgAsPng.svgAsPngUri(document.getElementById('trawlpackimage'));
      const sendIconUri = await svgAsPng.svgAsPngUri(document.getElementById('sendiconimage'));
      const receiveIconUri = await svgAsPng.svgAsPngUri(document.getElementById('receiveiconimage'));
      const packageIconUri = await svgAsPng.svgAsPngUri(document.getElementById('package'));
      const weightIconUri = await svgAsPng.svgAsPngUri(document.getElementById('weighing-machine'));
      const itemQRUri = await QRCode.toDataURL('ITM1237842734')
      const receiptQRUri = await QRCode.toDataURL('RCP1237842734')

      const doc = new JsPdf({
          unit: 'mm',
          format: [100, 165]
      });

      // dummy packages
      const packages = new Array(50).fill({})
      const lastIndex = packages.length - 1

      packages.forEach((pack, i) => {
  
        // text normal
        doc.setFont('times')
        doc.setTextColor('#000')
        doc.setFontSize(9);
        doc.text('TRAWL', 13, 13); // brand title
        doc.text('Kode pos: 1123', 9, 112) // postcode pengirim
        doc.text('Kode pos: 1134', 55, 112) // postcode penerima
        doc.setFontSize(8);
        doc.text('Ket Barang:', 55, 120)
        doc.text('Berat            :', 58, 126) // label only
        doc.text('Jumlah Koli :', 58, 132) // label only

        /**
         * bold text
        */
        doc.setFontSize(9);
        doc.setFont('times', '', 700)
        doc.text('PACK', 24.5, 13); // service title
        doc.text('Mitra Bisnis', 9, 18)
        doc.text('MB-JKT-0011', 9, 23)

        // from
        doc.text('DKI Jakarta', 9, 39)
        doc.text('Kota Adm. Jakarta Selatan', 9, 43, { maxWidth: 35 })
        
        // to
        doc.text('Jawa Timur', 9, 60)
        doc.text('Kabupaten Kediri', 9, 64, { maxWidth: 35 })
        
        // pengirim / sender
        doc.text('Hofifah Hayati', 9, 82)
        doc.text('+6281234343553', 9, 86)
        doc.text('Perumahan Karang Asem No.16 RT.15 / RW.17 Kel. Jati Bening Kec. Sampireun', 9, 90, { maxWidth: 35 })
        
        // penerima / receiver
        doc.text('Noimaah Sarifuad', 55, 82)
        doc.text('+6281545234344', 55, 86)
        doc.text('Perumahan Karang Asem No.16 RT.15 / RW.17 Kel. Jati Bening Kec. Sampireun', 55, 90, { maxWidth: 35 })
        
        doc.setFontSize(8)
        //no. barang
        doc.text('No. Barang: ITM1237842734', 9, 140)
        // no.resi
        doc.text('No. Resi: RCP1237842734', 55, 57, { maxWidth: 36 })

        doc.setFontSize(7)
        // berat value
        doc.text('1.072 Kg', 75, 126)
        // koli value
        doc.text('12', 75, 132)

        /**
         * end of bold text
         */

        // text with grey color
        doc.setTextColor('#94a2b8')
        doc.setFontSize(8);
        doc.text('Sat, 24 Jul 2021', 9, 29)
        doc.setFontSize(7);
        doc.text('Pengirim', 12, 77)
        doc.text('Penerima', 58, 77)
        doc.text('www.trawlbens.id', 50, 150)


        // text with red color
        doc.setFontSize(9);
        doc.setTextColor('#e60013')
        doc.text('Dari', 9, 35)
        doc.text('Ke', 9, 56)

        // rounded border
        doc.roundedRect(5, 5, 90, 155, 5, 5, 'S')

        // render image
        doc.addImage(trawlLogoUri, 'PNG', 60, 5, 20, 20);
        doc.addImage(trawlPackUri, 'PNG', 9, 10, 3, 3);
        doc.addImage(sendIconUri, "JPEG", 9, 75, 2, 2);
        doc.addImage(receiveIconUri, "JPEG", 55, 75, 2, 2);
        doc.addImage(weightIconUri, "JPEG", 55, 124, 2, 2);
        doc.addImage(packageIconUri, "JPEG", 55, 130, 2, 2);
        doc.addImage(receiptQRUri, "JPEG", 60, 32, 20, 20);
        doc.addImage(itemQRUri, "JPEG", 15, 115, 20, 20);

        if (i !== lastIndex) {
          doc.addPage()
        }
      })

      doc.save('test.pdf', { returnPromise: true }).then(() => {
        this.loading = false
      })
    },
    startPagination(event) {
      // console.log(event);
    },
    beforeDownload({ html2pdf, options, pdfContent }) {
      let worker = html2pdf()
        .set({
          ...options,
          ...this.options
        })
        .from(pdfContent)
        .toContainer()
        .toCanvas()
        .toPdf();
      worker.save().then(() => {
        this.loading = false;
      });
    },
    onProgress(event) {
      // console.log(event, "on Progress");
    },
    hasStartedGeneration(value) {
      // console.log("started", value);
    },
    hasGenerated(event) {
      // console.log(event);
    },
    hasSlot(slot = "") {
      return !!this.$slots[slot];
    },
    hasPaginated() {
      // console.log("Paginated");
    },
    hasDownloaded(event) {
      // console.log(event);
    },
    setFooter() {
      this.footer = !!this.$slots.footer ? undefined : null;
    }
  },
  watch: {
    value: function(value) {
      this.visible = value;
      this.$emit("input", value);
    },
    visible: function(value) {
      this.$emit("input", value);
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.setFooter();
      this.domRendered = true;
      this.contentHeight = this.content?.elm?.clientHeight
        ? this.content.elm.clientHeight
        : this.contentHeight;
    });
  }
};
</script>
