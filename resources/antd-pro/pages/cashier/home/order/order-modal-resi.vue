<template>
  <div>
    <a-button :class="['trawl-button-success']" @click="showModal" @cancel="onCancel">
      Print
    </a-button>
    <a-modal v-model="visible" :width="640" centered :footer="null">
      <template slot="closeIcon">
        <a-icon type="close" @click="onCancel"></a-icon>
      </template>
      <template slot="title">
        <h3>
          <a-space align="center" size="large">
            <b>ID Resi</b>
            <span @click="print" class="trawl-icon-clickable">
              <print-icon></print-icon
            ></span>
          </a-space>
        </h3>
      </template>
      <vue-html2pdf
        :show-layout="true"
        :float-layout="false"
        :enable-download="true"
        :preview-modal="false"
        :manual-pagination="true"
        :filename="`${code} - ${item.codes[0].content}`"
        pdf-format="a5"
        pdf-orientation="portrait"
        ref="html2Pdf"
        pdf-content-width="100%"
      >
        <section slot="pdf-content">
          <a-card id="resi">
            <!-- Nama Mitra -->
            <a-row type="flex">
              <a-col :span="12">
                <trawl-pack-with-text></trawl-pack-with-text>
                <h2 :style="{ margin: 0 }">
                  <b>{{ partnerInfo.code }}</b>
                </h2>
                <h4 style="font-weight: normal">{{ partnerInfo.address }}</h4>
                <h4 style="font-weight: normal" :class="['trawl-text-mute']">
                  {{ dateSimpleFormat(record.updated_at) }}
                </h4>
              </a-col>
              <a-col :span="12" :class="['trawl-card-white-label']">
                <trawl-white-icon></trawl-white-icon>
              </a-col>
            </a-row>

            <!-- From To Region -->
            <a-row type="flex" align="middle">
              <a-col :span="12">
                <a-space direction="vertical">
                  <div>
                    <h4 class="trawl-text-danger">Dari</h4>
                    <h2 :style="{ margin: 0 }">
                      <b>{{ origin_regency_name }}</b>
                    </h2>
                    <h3 class="trawl-text-normal">
                      {{ origin_province_name }}
                    </h3>
                  </div>

                  <div>
                    <h4 class="trawl-text-danger">Ke</h4>
                    <h2 :style="{ margin: 0 }">
                      <b>{{ destination_regency_name }}</b>
                    </h2>
                    <h3 class="trawl-text-normal">
                      {{ destination_province_name }}
                    </h3>
                  </div>
                </a-space>
              </a-col>
              <a-col :span="12" class="trawl-text-center">
                <vue-qrcode :value="code" />
                <h3 class="trawl-text-normal">No Resi: {{ code }}</h3>
              </a-col>
            </a-row>

            <a-divider></a-divider>

            <a-row type="flex" :gutter="[24, 24]">
              <!-- sender -->
              <a-col :span="12">
                <a-space direction="vertical" size="middle">
                  <div>
                    <h3 class="trawl-text-normal">
                      <a-space align="center">
                        <send-icon class="trawl-icon-scoped"></send-icon>
                        Pengirim
                      </a-space>
                    </h3>
                    <h3>
                      <b>{{ sender_name }}</b>
                    </h3>
                    <h3 class="trawl-text-normal">{{ sender_phone }}</h3>
                    <h3 class="trawl-text-normal">
                      {{ sender_address }}
                    </h3>
                  </div>

                  <!-- <h3 class="trawl-text-normal">Kode pos : 40256</h3> -->

                  <a-space direction="vertical" class="trawl-text-center">
                    <vue-qrcode :value="item.codes[0].content"></vue-qrcode>
                    <h3 class="trawl-text-normal">
                      No Barang: {{ item.codes[0].content }}
                    </h3>
                  </a-space>
                </a-space>
              </a-col>

              <!-- receiver -->
              <a-col :span="12">
                <a-space direction="vertical" size="middle">
                  <div>
                    <h3 class="trawl-text-normal">
                      <a-space align="center">
                        <receive-icon class="trawl-icon-scoped"></receive-icon>
                        Penerima
                      </a-space>
                    </h3>
                    <h3>
                      <b>{{ receiver_name }}</b>
                    </h3>
                    <h3 class="trawl-text-normal">
                      {{ receiver_phone }}
                    </h3>
                    <h3 class="trawl-text-normal">
                      {{ receiver_address }}
                    </h3>
                  </div>

                  <!-- <h3 class="trawl-text-normal">Kode pos : 52312</h3> -->

                  <a-space direction="vertical">
                    <h3 class="trawl-text-normal">
                      Ket Barang :
                      <b>{{ item.name }}</b>
                    </h3>
                    <h3 class="trawl-text-normal">
                      <a-space>
                        <package-icon></package-icon>
                        <span>
                          Berat :
                          <b>{{ item.weight }} Kg</b>
                        </span>
                      </a-space>
                    </h3>
                    <h3 class="trawl-text-normal">
                      <a-space>
                        <weight-machine-icon></weight-machine-icon>
                        <span>
                          Jml. Koli :
                          <b>{{ item.qty }} </b>
                        </span>
                      </a-space>
                    </h3>
                    <h3 :class="['trawl-text-normal', 'trawl-text-mute']">
                      www.trawlbens.id
                    </h3>
                  </a-space>
                </a-space>
              </a-col>
            </a-row>
          </a-card>
        </section>
      </vue-html2pdf>
    </a-modal>
  </div>
</template>
<script>
import {
  SendIcon,
  GpsIcon,
  TrawlPackIcon,
  TrawlPackWithText,
  TrawlWhiteIcon,
  QrCodeIcon,
  PackageIcon,
  WeightMachineIcon,
  PrintIcon,
} from "../../../../components/icons";
import ReceiveIcon from "../../../../components/icons/receiveIcon.vue";
import VueHtml2pdf from "vue-html2pdf";

export default {
  props: ["record", "partnerInfo"],
  data() {
    return {
      visible: false,
    };
  },
  computed: {
    item() {
      return this.record.items[0];
    },
    code() {
      return this.record?.code?.content;
    },
    updated_at() {
      return this.record?.updated_at;
    },
    origin_regency_name() {
      return this.record?.origin_regency?.name;
    },
    origin_province_name() {
      return this.record?.origin_regency?.province?.name;
    },

    destination_regency_name() {
      return this.record?.destination_regency?.name;
    },
    destination_province_name() {
      return this.record?.destination_regency?.province?.name;
    },

    sender_name() {
      return this.record?.sender_name;
    },
    sender_phone() {
      return this.record?.sender_phone;
    },
    sender_address() {
      return this.record?.sender_address;
    },
    receiver_name() {
      return this.record?.receiver_name;
    },
    receiver_phone() {
      return this.record?.receiver_phone;
    },
    receiver_address() {
      return this.record?.receiver_address;
    },
    items() {
      return this.record?.items;
    },
  },
  methods: {
    showModal() {
      this.visible = true;
    },
    print() {
      this.$refs.html2Pdf.generatePdf();
    },
    onCancel() {
      this.visible = false;
    },
  },
  components: {
    VueHtml2pdf,
    TrawlPackWithText,
    TrawlWhiteIcon,
  },
};
</script>
<style lang="less" scoped>
svg.trawl-icon-scoped {
  height: 27px !important;
  width: 27px !important;
}
</style>
