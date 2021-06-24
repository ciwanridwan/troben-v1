<template>
  <div>
    <trawl-modal-confirm v-model="confirmVisible" :cancelButton="false">
      <template slot="text"> Mitra telah ditugaskan </template>
    </trawl-modal-confirm>
    <trawl-modal-split v-model="visible">
      <template slot="title"> Assign Mitra </template>
      <template slot="trigger">
        <a-button type="success" class="trawl-button-success">
          <span>Assign Mitra</span>
        </a-button>
      </template>
      <template slot="left">
        <package-modal-detil :package="package" />
      </template>

      <template slot="rightHeader">
        <h3 class="trawl-text-bolder">Pilih Mitra</h3>
        <a-input-search
          v-model="filter.q"
          @change="searchPartner"
          @search="searchPartner"
          placeholder="Cari..."
        ></a-input-search>
      </template>

      <template slot="rightContent">
        <a-icon v-if="loading" type="loading" />
        <a-empty v-else-if="partners.length < 1" />
        <a-form-model v-else ref="formRules" :model="form" :rules="rules">
          <a-form-model-item prop="partner_hash" />
          <partner-radio-group :partners="partners" v-model="form.partner_hash" />
        </a-form-model>
      </template>
      <template slot="rightFooter">
        <a-button
          type="success"
          class="trawl-button-success"
          @click="assignPartner"
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
import PackageModalDetil from "../packages/package-modal-detil.vue";
import PartnerRadioButton from "../radio-buttons/partner-radio-button.vue";
import TrawlRadioButton from "../radio-buttons/trawl-radio-button.vue";
import PartnerRadioGroup from "../radio-buttons/partner-radio-group.vue";
import TrawlModalConfirm from "../trawl-modal-confirm.vue";
export default {
  components: {
    trawlModalSplit,
    OrderModalRowLayout,
    PackageModalDetil,
    PartnerRadioButton,
    TrawlRadioButton,
    PartnerRadioGroup,
    TrawlModalConfirm,
  },
  props: {
    value: {
      type: String,
      default: null,
    },
    package: {
      type: Object,
      default: () => {},
    },
    submitRoute: {
      type: String,
      default: "admin.home.assign",
    },
  },
  data() {
    return {
      SendIcon,
      partners: [],
      confirmVisible: false,
      visible: false,
      filter: {
        q: null,
        page: 1,
        per_page: 2,
      },
      form: {
        partner_hash: null,
      },
      rules: {
        partner_hash: [{ required: true, message: "Silahkan pilih mitra" }],
      },
      loading: false,
    };
  },
  methods: {
    searchPartner: _.debounce(async function () {
      this.loading = true;
      this.$http
        .get(this.routeUri(this.getRoute()), {
          params: {
            ...this.filter,
            partner: true,
            transporter_type: this.package?.transporter_type,
          },
        })
        .then(({ data }) => {
          this.partners = data.data;
        })
        .finally(() => (this.loading = false));
    }),
    async assignPartner() {
      let valid = await this.$refs.formRules.validate();
      console.log(valid);
      if (!valid) {
        return false;
      }

      this.$http
        .patch(
          this.routeUri(this.submitRoute, {
            package_hash: this.package.hash,
            partner_hash: this.form?.partner_hash,
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
        this.searchPartner();
      }
    },
    value: function (value) {
      this.form.partner_hash = value;
      this.$emit("input", value);
    },
    form: {
      handler: function (value) {
        this.$emit("input", value);
      },
      deep: true,
    },
  },
};
</script>
