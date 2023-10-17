<template>
  <a-popconfirm @confirm="confirm" ok-text="Kunjungi Halaman Admin">
    <template #title>
      Untuk saat ini silahkan gunakan Admin versi Terbaru.
    </template>
    <a-button type="success" class="trawl-button-success">
      <span>Ambil</span>
    </a-button>
  </a-popconfirm>
  <!-- <div>
    <trawl-modal-confirm v-model="confirmVisible" :cancelButton="false">
      <template slot="text"> Driver telah ditugaskan </template>
    </trawl-modal-confirm>
    <trawl-modal-split v-model="visible">
      <template slot="trigger">
        <a-button type="success" class="trawl-button-success">
          <span>Ambil</span>
        </a-button>
      </template>
      <template slot="left">
        <package-modal-detil :package="package" />
      </template>
      <template slot="leftBottom">
        <h4>Item</h4>
        <a-row :span="12">
          <a-col :span="9">Volume Barang</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ volumeItem }} cm</b></a-col
          >
        </a-row>
        <a-row :span="12">
          <a-col :span="9">Berat Aktual</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ weightActual }} kg</b></a-col
          >
        </a-row>
        <a-row :span="12">
          <a-col :span="9">Asuransi Barang</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ isInsured }}</b></a-col
          >
        </a-row>
        <a-row :span="12">
          <a-col :span="9">Perlindungan Extra</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ handling }}</b></a-col
          >
        </a-row>
        <a-row :span="12">
          <a-col :span="9">Metode Pengiriman</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ serviceType }}</b></a-col
          >
        </a-row>
        <a-row :span="12">
          <a-col :span="9">jenis Order</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ orderMode }}</b></a-col
          >
        </a-row>
        <br />
        <a-row>
          <a-col :span="12">
            <h4>Photo Terlampir</h4>
          </a-col>
        </a-row>
        <a-empty style="width: 100px" v-if="package.attachments[0] == null" />
        <div
          v-else
          style="
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: 1rem;
          "
        >
          <enlargeable-image
            style="width: 50px !important"
            v-for="(data, index) in URIImage"
            :key="index"
            :src="data.uri"
          />
        </div>
      </template>
      <template slot="rightHeader">
        <h3 class="trawl-text-bolder">Pilih Transporter</h3>
        <a-input-search
          v-model="filter.q"
          @change="searchTransporter"
          @search="searchTransporter"
          placeholder="Cari..."
        ></a-input-search>
      </template>

      <template slot="rightContent">
        <a-radio-group v-model="value" @change="onChange" :default-value="true">
          <a-radio :value="true"> Driver Sendiri </a-radio>
        </a-radio-group>
        <a-icon v-if="loading" type="loading" />
        <a-empty v-else-if="transporters.length < 1" />
        <a-form-model
          v-else-if="value && transporters.length > 0"
          ref="formRules"
          :model="form"
          :rules="rules"
        >
          <a-form-model-item prop="transporter_hash" />
          <transporter-radio-group
            :transporters="transporters"
            v-model="form.transporter_hash"
          />
        </a-form-model>
      </template>
      <template slot="rightFooter">
        <a-button
          type="success"
          class="trawl-button-success"
          @click="assignTransporter"
          block
          :loading="isLoading"
        >
          Tugaskan
        </a-button>
      </template>
    </trawl-modal-split>
  </div> -->
</template>
<script>
import OrderModalRowLayout from "../orders/order-modal-row-layout.vue";
import trawlModalSplit from "../trawl-modal-split.vue";
import { SendIcon } from "../icons";
import PackageModalDetil from "../packages/package-modal-detail.vue";
import TransporterRadioButton from "../radio-buttons/transporter-radio-button.vue";
import TrawlRadioButton from "../radio-buttons/trawl-radio-button.vue";
import TransporterRadioGroup from "../radio-buttons/transporter-radio-group.vue";
import EnlargeableImage from "@diracleo/vue-enlargeable-image";
import TrawlModalConfirm from "../trawl-modal-confirm.vue";
export default {
  components: {
    trawlModalSplit,
    EnlargeableImage,
    OrderModalRowLayout,
    PackageModalDetil,
    TransporterRadioButton,
    TrawlRadioButton,
    TransporterRadioGroup,
    TrawlModalConfirm,
  },
  props: {
    value: {
      type: String,
      default: null,
    },
    delivery: {
      type: Object,
      default: () => {},
    },
    submitRoute: {
      type: String,
      default: "partner.customer_service.home.order.assign",
    },
  },
  data() {
    return {
      isLoading: false,
      value: true,
      SendIcon,
      transporters: [],

      confirmVisible: false,
      visible: false,

      form: {
        transporter_hash: null,
        type: null,
      },
      rules: {
        transporter_hash: [{ required: true, message: "Silahkan pilih mitra" }],
      },

      filter: {
        q: null,
        page: 1,
        per_page: 2,
      },
      loading: false,
    };
  },
  computed: {
    package() {
      return this.delivery?.package;
    },
    URIImage() {
      if (this.package?.attachments[0] == null) {
        return null;
      } else {
        return this.package?.attachments;
      }
    },
    orderMode() {
      return this.delivery?.order_mode;
    },
    serviceType() {
      if (this.package?.service_code == "tps") {
        return "Reguler";
      }
      if (this.package?.service_code == "tpx") {
        return "Express";
      }
    },
    volumeItem() {
      return (
        this.package?.items[0]?.height *
        this.package?.items[0]?.length *
        this.package?.items[0]?.width
      );
    },
    weightActual() {
      return this.package?.items[0]?.weight;
    },
    handling() {
      return this.package?.items[0]?.handling?.[0]
        ? this.package?.items[0]?.handling[0]?.type
        : "Tidak ada ";
    },
    isInsured() {
      return this.package?.items[0]?.is_insured ? "Ya" : "Tidak";
    },
  },
  methods: {
    confirm() {
      window.open("https://admin.trawlbens.com/", "_blank");
    },
    onChange(e) {
      if (!e.target.value) {
        this.form.type = "independent";
      }
    },
    searchTransporter: _.debounce(async function () {
      this.loading = true;
      this.$http
        .get(this.routeUri(this.getRoute()), {
          params: {
            ...this.filter,
            transporter: true,
            type: this.package.transporter_type,
          },
        })
        .then(({ data }) => {
          this.transporters = data.data;
        })
        .finally(() => (this.loading = false));
    }),
    async assignTransporter() {
      this.isLoading = true;
      // let valid = await this.$refs.formRules.validate();
      // console.log(valid);
      // if (!valid) {
      //   return false;
      // }
      this.$http
        .patch(
          this.routeUri(this.submitRoute, {
            delivery_hash: this.delivery.hash,
            userable_hash: this.form.transporter_hash,
            type: this.form.type,
          })
        )
        .then(() => {
          this.visible = false;
          this.confirmVisible = true;
          this.isLoading = false;
          this.$emit("submit");
        });
    },
  },
  watch: {
    visible: function (value) {
      if (value) {
        this.searchTransporter();
      }
    },
    value: function (value) {
      this.transporter_hash = value;
      this.$emit("input", value);
    },
    transporter_hash: function (value) {
      this.$emit("input", value);
    },
  },
};
</script>

<style>
.ant-btn.ant-btn-sm {
  display: none;
}
.ant-btn.ant-btn-primary.ant-btn-sm {
  display: block !important;
}
</style>
