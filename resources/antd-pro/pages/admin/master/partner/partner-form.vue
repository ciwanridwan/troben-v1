<template>
  <content-layout>
    <template slot="title" :gutter="[10, 10]">
      <a-row type="flex" :gutter="48">
        <a-col :span="2">
          <a :href="routeUri('admin.master.partner')">
            <a-button icon="left" />
          </a>
        </a-col>
        <a-col>
          <h3>Tambah Mitra</h3>
        </a-col>
      </a-row>
    </template>

    <template slot="content">
      <a-card id="partner-form">
        <a-form-model ref="ruleForm" :rules="rules" :model="form">
          <a-row type="flex">
            <a-col :span="8">
              <trawl-input label="Jenis Mitra">
                <template slot="input">
                  <a-form-model-item ref="type" prop="type">
                    <a-select :default-value="form.type" v-model="form.type">
                      <a-select-option
                        v-for="type in partner_types"
                        :key="type"
                        :value="type"
                      >
                        {{ type }}
                      </a-select-option>
                    </a-select>
                  </a-form-model-item>
                </template>
              </trawl-input>
            </a-col>
          </a-row>
        </a-form-model>

        <partner-form-location
          ref="location"
          :geo="geo"
        ></partner-form-location>

        <div v-if="form.type !== null">
          <partner-transporter-form
            v-if="form.type == 'transporter'"
            :geo="geo"
          ></partner-transporter-form>
        </div>

        <hr />

        <partner-owner-form ref="owner"></partner-owner-form>

        <div v-if="form.type !== null" class="addon">
          <hr />
          <partner-space-form
            ref="space"
            v-if="addon_space >= 0"
          ></partner-space-form>

          <inventory ref="inventory" v-if="addon_inventory >= 0"></inventory>

          <transporters
            ref="transporter"
            v-if="addon_transporter >= 0"
            :transporter-types="transporter_types"
          ></transporters>
        </div>

        <hr v-else />

        <a-row type="flex" justify="end" :gutter="[10, 10]">
          <a-col>
            <a-button> Batal </a-button>
          </a-col>
          <a-col>
            <a-button type="primary" @click="onPost"> Simpan </a-button>
          </a-col>
        </a-row>
      </a-card>
    </template>
  </content-layout>
</template>
<script>
import TrawlInput from "../../../../components/trawl-input.vue";
import contentLayout from "../../../../layouts/content-layout.vue";
import Inventory from "./inventory/inventory";
import PartnerFormLocation from "./partner-form-location.vue";
import PartnerOwnerForm from "./partner-owner-form.vue";
import PartnerSpaceForm from "./space/partner-space-form.vue";
import PartnerTransporterForm from "./transporter/partner-transporter-form.vue";
import Transporters from "./transporter/transporters.vue";
import { serialize } from "object-to-formdata";

export default {
  components: {
    contentLayout,
    PartnerFormLocation,
    PartnerTransporterForm,
    PartnerOwnerForm,
    Transporters,
    Inventory,
    PartnerSpaceForm,
    TrawlInput
  },
  data() {
    return {
      geo: {},
      partner_types: [],
      transporter_types: [],
      valid: false,
      form: {
        type: null,
        name: "test"
      },
      rules: {
        type: [{ required: true }],
        name: [{ required: true }]
      }
    };
  },
  methods: {
    onSuccessResponse(resp) {
      let { data } = resp;

      this.geo = data.geo;
      this.partner_types = data.partner_types;
      this.transporter_types = data.transporter_types;
    },
    onSuccessStore(resp) {
      this.$notification.success({
        message: "Partner Has Been Created"
      });
      window.location.href = this.routeUri("admin.master.partner");
    },

    onPost() {
      let form = {
        headers: {
          "Content-Type": "multipart/form-data"
        },
        ...this.partnerForm,
        ...this.ownerForm
      };

      switch (this.form.type) {
        case "pool":
          form = { ...form, ...this.poolData };
          break;
        case "business":
          form = { ...form, ...this.businessData };
          break;
        case "space":
          form = { ...form, ...this.spaceData };
          break;
        case "transporter":
          form = { ...form, ...this.transporterData };
          break;
      }

      if (this.valid) {
        this.$http
          .post(this.routeUri(this.getRoute()), form)
          .then(this.onSuccessStore)
          .catch(this.onErrorValidation);
      } else {
        this.$notification.error({
          message: "Silahkan isi form tersebut"
        });
      }
    }
  },
  created() {
    this.getItems();
  },
  computed: {
    partnerForm() {
      let location = this.$refs.location;
      location.$refs.ruleForm.validate(valid => {
        this.valid = valid;
      });

      this.$refs.ruleForm.validate(valid => {
        this.valid = valid;
      });

      return {
        partner: {
          ...location.$data.form,
          ...this.form
        }
      };
    },

    ownerForm() {
      let owner = this.$refs.owner;
      owner.$refs.ruleForm.validate(valid => {
        this.valid = valid;
      });
      return { owner: owner.$data.form, photo: owner.$data.form.photo };
    },
    transporterForm() {
      return { transporter: this.$refs.transporter.$data.form };
    },
    inventoryForm() {
      return { inventory: this.$refs.inventory.$data.form };
    },
    spaceForm() {
      let space = this.$refs.space;
      space.$refs.ruleForm.validate(valid => {
        this.valid = valid;
      });
      return { warehouse: space.$data.form };
    },
    poolData() {
      return {
        ...this.spaceForm,
        ...this.inventoryForm
      };
    },
    spaceData() {
      return {
        ...this.spaceForm
      };
    },
    transporterData() {
      return {
        ...this.transporterForm
      };
    },
    businessData() {
      return {
        ...this.spaceForm,
        ...this.inventoryForm,
        ...this.transporterForm
      };
    },
    addon_inventory() {
      return _.indexOf(["business", "pool"], this.form.type);
    },
    addon_space() {
      return _.indexOf(["business", "space", "pool"], this.form.type);
    },
    addon_transporter() {
      return _.indexOf(["business", "transporter"], this.form.type);
    }
  }
};
</script>

<style lang="scss">
#partner-form {
  .ant-card-body > * {
    margin-bottom: 24px;
  }
  .addon > * {
    margin-bottom: 24px;
  }
}
</style>
