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
        <a-row type="flex">
          <a-col :span="8">
            <h3>Jenis Mitra</h3>
            <a-select
              :default-value="form.partner_type"
              v-model="form.partner_type"
            >
              <a-select-option
                v-for="type in partner_types"
                :key="type"
                :value="type"
              >
                {{ type }}
              </a-select-option>
            </a-select>
          </a-col>
        </a-row>
        <partner-form-location
          ref="location"
          :geo="geo"
        ></partner-form-location>

        <div v-if="form.partner_type !== null">
          <partner-transporter-form
            v-if="form.partner_type == 'transporter'"
            :geo="geo"
          ></partner-transporter-form>
        </div>

        <hr />

        <partner-owner-form ref="owner"></partner-owner-form>

        <div v-if="form.partner_type !== null" class="addon">
          <hr />
          <partner-space-form
            ref="addon"
            v-if="addon_space >= 0"
          ></partner-space-form>

          <inventory ref="addon" v-if="addon_inventory >= 0"></inventory>

          <transporters
            ref="addon"
            v-if="addon_transporter >= 0"
          ></transporters>
        </div>

        <hr v-else />

        <a-row type="flex" justify="end" :gutter="[10, 10]">
          <a-col>
            <a-button>
              Batal
            </a-button>
          </a-col>
          <a-col>
            <a-button type="primary" @click="onPost">
              Simpan
            </a-button>
          </a-col>
        </a-row>
      </a-card>
    </template>
  </content-layout>
</template>
<script>
import contentLayout from "../../../../layouts/content-layout.vue";
import Inventory from "./inventory/inventory";
import PartnerFormLocation from "./partner-form-location.vue";
import PartnerOwnerForm from "./partner-owner-form.vue";
import PartnerSpaceForm from "./space/partner-space-form.vue";
import PartnerTransporterForm from "./transporter/partner-transporter-form.vue";
import Transporters from "./transporter/transporters.vue";
export default {
  components: {
    contentLayout,
    PartnerFormLocation,
    PartnerTransporterForm,
    PartnerOwnerForm,
    Transporters,
    Inventory,
    PartnerSpaceForm
  },
  data() {
    return {
      geo: {},
      partner_types: [],
      form: {
        partner_type: null
      }
    };
  },
  methods: {
    onSuccessResponse(resp) {
      let { data } = resp;
      this.geo = data.geo;
      this.partner_types = data.partner_types;
    },
    storePartnerTransporter() {
      return "test";
    },
    onPost() {
      let location = { ...this.$refs.location.$data.form };
      let owner = { ...this.$refs.owner.$data.form };
      let form = {
        partner: { ...location, partner_type: this.form.partner_type },
        owner: { ...owner }
      };
      this.storePartnerTransporter();
      console.log(form);
    }
  },
  created() {
    this.getItems();
  },
  computed: {
    addon_inventory() {
      console.log(
        _.indexOf(["business", "pool", "warehouse"], this.form.partner_type)
      );
      return _.indexOf(
        ["business", "pool", "warehouse"],
        this.form.partner_type
      );
    },
    addon_space() {
      return _.indexOf(
        ["business", "space", "warehouse"],
        this.form.partner_type
      );
    },
    addon_transporter() {
      return _.indexOf(["business", "transporter"], this.form.partner_type);
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
