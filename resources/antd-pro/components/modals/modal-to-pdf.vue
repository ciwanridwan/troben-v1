<template>
  <div>
    <loading-modal v-model="loading" />
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
import JsPdf from "jspdf";
import svgAsPng from "save-svg-as-png";
import QRCode from "qrcode";
import LoadingModal from "../orders/modal/loading-modal.vue";
import {getDestinationAddress} from "../../functions/orders";
import {getPartnerByType} from "../../functions/partnerType";
import moment from "moment";
export default {
  components: {
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
    },
    package: {
      type: Object,
      default: () => {}
    },
    items: {
      type: Array,
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
    partner() {
      return this.$laravel.user.partners[0];
    },
    content() {
      return this.$slots?.content ? this.$slots.content[0] : null;
    }
  },
  methods: {
    partnerAddress() {
      return this.partner?.address;
    },
    partnerCode() {
      return this.partner?.code;
    },
    partnerType() {
      let partner = getPartnerByType(this.partner?.type);
      return partner?.title;
    },
    receiver_zip_code() {
      return this.package?.destination_sub_district?.zip_code;
    },
    receiver_address() {
      // return `${this.package?.receiver_address} ${getDestinationAddress(this.package)}`;
      return this.package?.receiver_address;
    },
    receiver_way_point() {
        return this.package?.receiver_way_point;
      },
    receiver_phone() {
      return this.package?.receiver_phone;
    },
    receiver_name() {
      return this.package?.receiver_name;
    },
    sender_zip_code() {
      return this.package?.origin_sub_district?.zip_code ?? '';
    },
    sender_address() {
      return this.package?.sender_address;
    },
    sender_phone() {
      return this.package?.sender_phone;
    },
    sender_name() {
      return this.package?.sender_name;
    },
    sender_way_point() {
        return this.package?.sender_way_point;
      },
    origin_regency_name() {
      return this.package?.origin_regency?.name;
    },
    origin_province_name() {
      return this.package?.origin_regency?.province?.name;
    },

    destination_regency_name() {
      return this.package?.destination_regency?.name;
    },
    destination_province_name() {
      return this.package?.destination_regency?.province?.name;
    },
    packageCode() {
      return this.package?.code?.content;
    },
    dateSimpleFormat(date) {
      return moment(date).format("ddd, DD MMM YYYY");
    },
    showModal() {
      this.visible = true;
    },
    hideModal() {
      this.visible = false;
    },
    async saveToPdf() {
      this.loading = true;

      //get uri png converted from svg asset
      const trawlLogoUri = await svgAsPng.svgAsPngUri(document.getElementById('trawllabelimage'));
      const trawlPackUri = await svgAsPng.svgAsPngUri(document.getElementById('trawlpackimage'));
      const sendIconUri = await svgAsPng.svgAsPngUri(document.getElementById('sendiconimage'));
      const receiveIconUri = await svgAsPng.svgAsPngUri(document.getElementById('receiveiconimage'));
      const packageIconUri = await svgAsPng.svgAsPngUri(document.getElementById('package'));
      const weightIconUri = await svgAsPng.svgAsPngUri(document.getElementById('weighing-machine'));

      const doc = new JsPdf({
          unit: 'mm',
          format: [100, 165]
      });

      const itemsLength = this.items.length;
      let date = this.dateSimpleFormat(new Date());
      let idx = 0

      for await (const item of this.items) {
        idx++
        let weight_borne_total = item?.weight_borne_total.toString() ?? '0';
        let desc = item?.desc ?? '';
        let qty = item?.qty.toString() ?? '1';
        let idy = 0;
        let codeLength = item.codes?.length;

        for (const code of item.codes) {
          idy++
          // text normal
          doc.setFont('times')
          doc.setTextColor('#000')
          doc.setFontSize(9);
          doc.text('TRAWL', 13, 13); // brand title
          // doc.text(`Kode pos: ${ this.sender_zip_code() }`, 9, 112) // postcode pengirim
          doc.text(`Kode pos: ${ this.receiver_zip_code() }`, 55, 115) // postcode penerima
          doc.setFontSize(8);
          doc.text('Ket Barang       :', 55, 120)
          doc.text('Berat            :', 58, 126) // label only
          doc.text('Jumlah Koli :', 58, 132) // label only
          /**
           * bold text
           */
          doc.setFontSize(9);
          doc.setFont('times', '', 700)
          doc.text('PACK', 24.5, 13); // service title
          doc.text(this.partnerType(), 9, 18)
          doc.text(this.partnerCode(), 9, 23)

          // from
          doc.text(this.origin_province_name(), 9, 39)
          doc.text(this.origin_regency_name(), 9, 43, { maxWidth: 36 })

          // to
          doc.text(this.destination_province_name(), 9, 60)
          doc.text(this.destination_regency_name(), 9, 64, { maxWidth: 36 })

          // pengirim / sender
          doc.text(this.sender_name(), 9, 82)
          doc.text(this.sender_phone(), 9, 86)
          doc.text(this.sender_address(), 9, 90, { maxWidth: 36 })
          doc.text(`Note: ${this.sender_way_point()}`, 9, 105, {maxWidth: 36});

          // penerima / receiver
          doc.text(this.receiver_name(), 55, 82)
          doc.text(this.receiver_phone(), 55, 86)
          doc.text(this.receiver_address(), 55, 90, { maxWidth: 36 })
          doc.text(`Note: ${this.receiver_way_point()}`, 55, 105, {maxWidth: 36});

          doc.setFontSize(8)
          //no. barang
          doc.text(`No. Barang: ${code.content}`, 9, 140)
          // no.resi
          doc.text(`No. Resi: ${this.packageCode()}`, 55, 57, { maxWidth: 36 })

          doc.setFontSize(7)
          doc.text(desc, 75, 120)
          // berat value
          doc.text(weight_borne_total, 75, 126)
          // koli value
          doc.text(qty, 75, 132)

          /**
           * end of bold text
           */
          // text with grey color
          doc.setTextColor('#94a2b8')
          doc.setFontSize(8);
          doc.text(date, 9, 29)
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

          const itemQRUri = await QRCode.toDataURL(code.content)
          const receiptQRUri = await QRCode.toDataURL(this.packageCode())

          doc.addImage(receiptQRUri, "JPEG", 60, 32, 20, 20);
          doc.addImage(itemQRUri, "JPEG", 15, 115, 20, 20);

          if (idx < itemsLength || idy < codeLength) {
            doc.addPage()
          }
        }
      }
      doc.save(this.fileName, { returnPromise: true }).then(() => {
        this.loading = false
      })
    },
    hasSlot(slot = "") {
      return !!this.$slots[slot];
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
