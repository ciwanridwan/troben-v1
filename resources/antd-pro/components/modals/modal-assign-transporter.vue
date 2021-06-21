<template>
  <trawl-modal-split v-model="visible">
    <template slot="trigger">
      <a-button type="success" class="trawl-button-success">
        <span>Ambil</span>
      </a-button>
    </template>
    <template slot="left">
      <package-modal-detil :package="package" />
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
      <a-icon v-if="loading" type="loading" />
      <a-empty v-else-if="transporters.length < 1" />
      <transporter-radio-group
        v-else
        :transporters="transporters"
        v-model="transporter_hash"
      />
    </template>
    <template slot="rightFooter">
      <a-button
        type="success"
        class="trawl-button-success"
        @click="assignTransporter"
        block
      >
        Tugaskan
      </a-button>
    </template>
  </trawl-modal-split>
</template>
<script>
import OrderModalRowLayout from "../orders/order-modal-row-layout.vue";
import trawlModalSplit from "../trawl-modal-split.vue";
import { SendIcon } from "../icons";
import PackageModalDetil from "../packages/package-modal-detil.vue";
import TransporterRadioButton from "../radio-buttons/transporter-radio-button.vue";
import TrawlRadioButton from "../radio-buttons/trawl-radio-button.vue";
import TransporterRadioGroup from "../radio-buttons/transporter-radio-group.vue";
export default {
  components: {
    trawlModalSplit,
    OrderModalRowLayout,
    PackageModalDetil,
    TransporterRadioButton,
    TrawlRadioButton,
    TransporterRadioGroup,
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
  },
  data() {
    return {
      SendIcon,
      transporters: [],
      visible: false,
      transporter_hash: null,
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
  },
  methods: {
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
    assignTransporter() {
      this.$http
        .patch(
          this.routeUri("partner.customer_service.home.order.assign", {
            delivery_hash: this.delivery.hash,
            userable_hash: this.transporter_hash,
          })
        )
        .then(() => {
          this.$notification.success({
            message: "Berhasil Menugaskan Driver",
          });
          this.visible = false;
          this.$emit("assigned");
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
