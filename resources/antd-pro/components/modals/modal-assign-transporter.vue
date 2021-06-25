<template>
  <div>
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
        <a-form-model v-else ref="formRules" :model="form" :rules="rules">
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
        >
          Tugaskan
        </a-button>
      </template>
    </trawl-modal-split>
  </div>
</template>
<script>
import OrderModalRowLayout from "../orders/order-modal-row-layout.vue";
import trawlModalSplit from "../trawl-modal-split.vue";
import { SendIcon } from "../icons";
import PackageModalDetil from "../packages/package-modal-detail.vue";
import TransporterRadioButton from "../radio-buttons/transporter-radio-button.vue";
import TrawlRadioButton from "../radio-buttons/trawl-radio-button.vue";
import TransporterRadioGroup from "../radio-buttons/transporter-radio-group.vue";
import TrawlModalConfirm from "../trawl-modal-confirm.vue";
export default {
  components: {
    trawlModalSplit,
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
      SendIcon,
      transporters: [],

      confirmVisible: false,
      visible: false,

      form: {
        transporter_hash: null,
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
    async assignTransporter() {
      let valid = await this.$refs.formRules.validate();
      console.log(valid);
      if (!valid) {
        return false;
      }

      this.$http
        .patch(
          this.routeUri(this.submitRoute, {
            delivery_hash: this.delivery.hash,
            userable_hash: this.form.transporter_hash,
          })
        )
        .then(() => {
          this.visible = false;
          this.confirmVisible = true;
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
