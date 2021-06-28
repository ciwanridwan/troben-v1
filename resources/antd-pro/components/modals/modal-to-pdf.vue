<template>
  <div>
    <vue-html2pdf
      :filename="fileName"
      :enable-download="false"
      :show-layout="false"
      :manual-pagination="true"
      :pdf-quality="2"
      pdf-format="a4"
      pdf-orientation="portrait"
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
export default {
  components: {
    VueHtml2pdf
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
    }
  },
  data() {
    return {
      visible: false,
      contentHeight: 1400,
      domRendered: false,
      footer: undefined
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
    saveToPdf() {
      this.$refs.html2Pdf.generatePdf();
    },
    startPagination(event) {
      console.log(event);
    },
    beforeDownload({ html2pdf, options, pdfContent }) {
      let worker = html2pdf()
        .set(options)
        .from(pdfContent)
        .toPdf();
      worker = worker
        .get("pdf")
        // .then(function (pdf) {
        //   pdf.addPage();
        // })
        .toContainer()
        .toCanvas()
        .toPdf();
      // console.log(event);
      // return false;
      // pages.slice(1).forEach(function (page) {
      //   worker = worker
      //     .get("pdf")
      //     .then(function (pdf) {
      //       pdf.addPage();
      //     })
      //     .from(page)
      //     .toContainer()
      //     .toCanvas()
      //     .toPdf();
      // });
      worker = worker.save();
    },
    onProgress(event) {
      console.log(event, "on Progress");
    },
    hasStartedGeneration(value) {
      console.log("started", value);
    },
    hasGenerated(event) {
      console.log(event);
    },
    hasSlot(slot = "") {
      return !!this.$slots[slot];
    },
    hasPaginated() {
      console.log("Paginated");
    },
    hasDownloaded(event) {
      console.log(event);
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
