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
            <a-select :default-value="form.type" v-model="form.type">
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
        type: null,
        name: "test"
      }
    };
  },
  methods: {
    onSuccessResponse(resp) {
      let { data } = resp;
      this.geo = data.geo;
      this.partner_types = data.partner_types;
    },
    onSuccessStore(resp) {
      console.log(resp);
    },
    onErrorStore(err) {
      console.log(err.response);
    },
    storePartnerTransporter() {
      return "test";
    },
    onPost() {
      let location = { ...this.$refs.location.$data.form };
      let owner = { ...this.$refs.owner.$data.form };
      let form = {
        partner: { ...location, ...this.form },
        owner: { ...owner }
      };
      this.$http
        .post(this.routeUri(this.getRoute()), form)
        .then(this.onSuccessStore)
        .catch(this.onErrorStore);
    }
  },
  created() {
    this.getItems();
  },
  computed: {
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
