<template>
  <content-layout>
    <template slot="title">
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
      <a-card>
        <a-row>
          <a-col :span="8">
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
        <a-row type="flex">
          <a-col :span="8">
            <a-select
              :default-value="form.geo.province"
              v-model="form.geo.province"
            >
              <a-select-option
                v-for="province in geo.provinces"
                :key="province.id"
                :value="province.id"
              >
                {{ province.name }}
              </a-select-option>
            </a-select>
          </a-col>
          <a-col :span="8">
            <a-select
              :default-value="form.geo.regency"
              v-model="form.geo.regency"
            >
              <a-select-option
                v-for="regency in regencies"
                :key="regency.id"
                :value="regency.id"
              >
                {{ regency.name }}
              </a-select-option>
            </a-select>
          </a-col>
          <a-col :span="8">
            <a-select
              :default-value="form.geo.district"
              v-model="form.geo.district"
            >
              <a-select-option
                v-for="district in districts"
                :key="district.id"
                :value="district.id"
              >
                {{ district.name }}
              </a-select-option>
            </a-select>
          </a-col>
        </a-row>
      </a-card>
    </template>
  </content-layout>
</template>
<script>
import contentLayout from "../../../../layouts/content-layout.vue";
export default {
  components: { contentLayout },
  data() {
    return {
      geo: {},
      partner_types: [],
      form: {
        partner_type: null,
        geo: {
          regency: null,
          province: null,
          district: null,
          sub_district: null,
          zip_code: null
        },
        address: null
      }
    };
  },
  computed: {
    regencies() {
      return _.filter(this.geo.regencies, {
        province_id: this.form.geo.province
      });
    },
    districts() {
      return _.filter(this.geo.districts, {
        regency_id: this.form.geo.regency
      });
    }
  },
  methods: {
    onSuccessResponse(resp) {
      let { data } = resp;
      this.geo = data.geo;
      this.partner_types = data.partner_types;
    }
  },
  created() {
    this.getItems();
  }
};
</script>
