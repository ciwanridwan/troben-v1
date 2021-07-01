<template>
  <content-layout>
    <template slot="content">
      <trawl-modal-confirm
        v-model="visible"
        :cancelButton="false"
        :okButton="false"
      >
        <template slot="text"> Berhasil Membuat Mitra </template>
      </trawl-modal-confirm>
      <a-form-model ref="formRules" :model="form" :rules="rules">
        <a-card>
          <a-space direction="vertical">
            <a-row type="flex">
              <a-col :span="8">
                <a-space :style="{ width: '100%' }" direction="vertical">
                  <h3 class="trawl-text-normal trawl-text-bold">Jenis Mitra</h3>
                  <a-form-model-item prop="type">
                    <partner-form-type v-model="form.type" />
                  </a-form-model-item>
                </a-space>
              </a-col>
            </a-row>

            <a-form-model-item prop="location">
              <partner-form-location :geo="geo" v-model="form.location" />
            </a-form-model-item>

            <a-row v-if="isTypeTransporter" type="flex">
              <a-col :span="8">
                <a-form-model-item prop="transporter.responsibles">
                  <partner-form-responsible-area
                    :regencies="regencies"
                    v-model="form.transporter.responsibles"
                  />
                </a-form-model-item>
              </a-col>
            </a-row>
          </a-space>

          <a-divider />

          <a-form-model-item prop="owner">
            <partner-owner-form v-model="form.owner" />
          </a-form-model-item>

          <template v-if="isTypeSpace">
            <a-divider />

            <a-form-model-item prop="space">
              <partner-space-form v-model="form.space" />
            </a-form-model-item>
          </template>

          <template v-if="isTypePool">
            <a-divider />
            <a-form-model-item prop="warehouse.inventories">
              <partner-inventory v-model="form.warehouse.inventories" />
            </a-form-model-item>
          </template>

          <template v-if="isTypeTransporter">
            <a-form-model-item prop="transporter.transporters">
              <partner-transporters
                :transporterTypes="transporter_types"
                v-model="form.transporter.transporters"
              />
            </a-form-model-item>
          </template>

          <a-row type="flex" justify="end">
            <a-col :span="8">
              <a-button
                type="success"
                class="trawl-button-success"
                block
                @click="submit"
              >
                Simpan
              </a-button>
            </a-col>
          </a-row>
        </a-card>
      </a-form-model>
    </template>
  </content-layout>
</template>
<script>
import {
  TYPE_BUSINESS,
  TYPE_POOL,
  TYPE_SPACE,
  TYPE_TRANSPORTER
} from "../../../../data/partnerType";
import PartnerFormType from "../../../../components/partners/form/partner-form-type.vue";
import contentLayout from "../../../../layouts/content-layout.vue";
import PartnerFormLocation from "../../../../components/partners/form/partner-form-location.vue";
import PartnerFormResponsibleArea from "../../../../components/partners/form/transporter/partner-form-responsible-area.vue";
import PartnerOwnerForm from "../../../../components/partners/form/partner-owner-form.vue";
import PartnerTransporters from "../../../../components/partners/form/transporter/partner-transporters.vue";
import PartnerSpaceForm from "../../../../components/partners/form/space/partner-space-form.vue";
import TrawlModalConfirm from "../../../../components/trawl-modal-confirm.vue";
export default {
  components: {
    contentLayout,
    PartnerFormType,
    PartnerFormLocation,
    PartnerFormResponsibleArea,
    PartnerOwnerForm,
    PartnerTransporters,
    PartnerSpaceForm,
    TrawlModalConfirm
  },
  data() {
    return {
      geo: {},
      partner_types: [],
      transporter_types: [],
      visible: false,
      form: {
        type: null,
        location: null,
        transporter: {
          responsibles: null,
          transporters: []
        },
        warehouse: {
          inventories: []
        },
        owner: null,
        space: null
      },
      rules: {
        type: [{ required: true, trigger: ["change", "blur"] }],
        location: [{ required: true, trigger: ["change", "blur"] }],
        transporter: [{ required: true, trigger: ["change", "blur"] }],
        "transporter.responsibles": [
          { required: true, trigger: ["change", "blur"] }
        ],
        // "transporter.transporters": [{ required: true ,trigger:['change','blur']}],
        warehouse: [{ required: true, trigger: ["change", "blur"] }],
        // "warehouse.inventories": [{ required: true ,trigger:['change','blur']}],
        owner: [{ required: true, trigger: ["change", "blur"] }],
        space: [{ required: true, trigger: ["change", "blur"] }]
      }
    };
  },
  computed: {
    regencies() {
      return this.geo?.regencies ?? [];
    },
    isTypeTransporter() {
      return [TYPE_TRANSPORTER, TYPE_BUSINESS].indexOf(this.form?.type) > -1;
    },
    isTypePool() {
      return [TYPE_POOL, TYPE_BUSINESS].indexOf(this.form?.type) > -1;
    },
    isTypeSpace() {
      return (
        [TYPE_SPACE, TYPE_POOL, TYPE_BUSINESS].indexOf(this.form?.type) > -1
      );
    },
    partner() {
      return {
        name: this.form?.owner?.name,
        contact_email: this.form?.owner?.email,
        contact_phone: this.form?.owner?.phone,
        type: this.form.type,
        ...this.form.location
      };
    },
    warehouse() {
      return {
        ...this.form.warehouse,
        ...this.form.space
      };
    },
    transporter() {
      return {
        ...this.form.transporter.transporters
      };
    },
    sendForm() {
      return {
        ...this.form,
        partner: this.partner,
        warehouse: this.warehouse,
        transporter: this.transporter
      };
    }
  },
  methods: {
    onSuccessResponse(resp) {
      let { data } = resp;

      this.geo = data.geo;
      this.partner_types = data.partner_types;
      this.transporter_types = data.transporter_types;
    },
    submit() {
      this.$refs.formRules.validate(valid => {
        if (valid) {
          this.$http
            .post(this.routeUri("admin.master.partner.store"), this.sendForm)
            .then(() => {
              this.visible = true;
              setTimeout(() => {
                window.location.href = this.routeUri("admin.master.partner");
              }, 3000);
            })
            .catch(error => this.onErrorValidation(error));
        }
      });
    }
  },
  watch: {
    form: {
      handler: function(value) {
        // this.$refs.formRules.validate();
      },
      deep: true
    }
  },
  mounted() {
    this.getItems();
  }
};
</script>
