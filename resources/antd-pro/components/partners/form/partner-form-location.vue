<template>
  <div>
    <h2>Lokasi Mitra</h2>

    <a-form-model
      ref="formRules"
      :rules="rules"
      :model="form"
      layout="vertical"
      hideRequiredMark
    >
      <a-row type="flex" :gutter="[10, 10]">
        <a-col :span="8">
          <a-form-model-item ref="province" prop="geo_province_id">
            <template slot="label">
              <h3>Provinsi</h3>
            </template>
            <a-select
              show-search
              :filter-option="filterOptionMethod"
              v-model="form.geo_province_id"
              placeholder="- pilih provinsi -"
            >
              <a-select-option
                v-for="province in geo.provinces"
                :key="province.id"
                :value="province.id"
              >
                {{ province.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>
        </a-col>
        <a-col :span="8">
          <a-form-model-item ref="regency" prop="geo_regency_id">
            <template slot="label">
              <h3>Kota / Kabupaten</h3>
            </template>
            <a-select
              show-search
              :filter-option="filterOptionMethod"
              v-model="form.geo_regency_id"
              placeholder="- pilih kota/kabupaten -"
            >
              <a-select-option
                v-for="regency in regencies"
                :key="regency.id"
                :value="regency.id"
              >
                {{ regency.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>
        </a-col>
        <a-col :span="8">
          <a-form-model-item ref="district" prop="geo_district_id">
            <template slot="label">
              <h3>Kecamatan</h3>
            </template>
            <a-select
              show-search
              :filter-option="filterOptionMethod"
              v-model="form.geo_district_id"
              placeholder="- pilih kecamatan -"
            >
              <a-select-option
                v-for="district in districts"
                :key="district.id"
                :value="district.id"
              >
                {{ district.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>
        </a-col>
        <a-col :span="8">
          <a-form-model-item ref="sub_district" prop="geo_sub_district_id">
            <template slot="label">
              <h3>Kelurahan</h3>
            </template>
            <a-select
              show-search
              :filter-option="filterOptionMethod"
              v-model="form.geo_sub_district_id"
              placeholder="- pilih kelurahan -"
            >
              <a-select-option
                v-for="sub_district in sub_districts"
                :key="sub_district.id"
                :value="sub_district.id"
              >
                {{ sub_district.name }}
              </a-select-option>
            </a-select>
          </a-form-model-item>
        </a-col>
        <a-col :span="8">
          <a-form-model-item ref="zip_code" prop="zip_code">
            <template slot="label">
              <h3>Kode Pos</h3>
            </template>
            <a-input-number v-model="form.zip_code" disabled></a-input-number>
          </a-form-model-item>
        </a-col>
        <a-col :span="16">
          <trawl-input label="Alamat Lengkap">
            <template slot="input">
              <a-form-model-item ref="address" prop="address">
                <a-textarea v-model="form.address" :rows="5"></a-textarea>
              </a-form-model-item>
            </template>
          </trawl-input>
        </a-col>
      </a-row>
    </a-form-model>
  </div>
</template>
<script>
import TrawlInput from "../../trawl-input.vue";
export default {
  components: { TrawlInput },
  props: {
    geo: {
      type: Object
    }
  },
  data() {
    return {
      form: {
        geo_regency_id: null,
        geo_province_id: null,
        geo_district_id: null,
        geo_sub_district_id: null,
        zip_code: null,
        address: null
      },
      rules: {
        geo_regency_id: [{ required: true }],
        geo_province_id: [{ required: true }],
        geo_district_id: [{ required: true }],

        /// MASALAH

        geo_sub_district_id: [{ required: true }],

        /// MASALAH

        zip_code: [{ required: true }]
      }
    };
  },

  computed: {
    regencies() {
      return _.filter(this.geo.regencies, {
        province_id: this.form.geo_province_id
      });
    },
    districts() {
      return _.filter(this.geo.districts, {
        regency_id: this.form.geo_regency_id
      });
    },
    sub_districts() {
      return _.filter(this.geo.sub_districts, {
        district_id: this.form.geo_district_id
      });
    },
    zip_code() {
      let sub_district = _.find(this.geo.sub_districts, {
        id: this.form.geo_sub_district_id
      });
      return sub_district ? sub_district.zip_code : "";
    }
  },
  watch: {
    "form.geo_sub_district_id": function(value) {
      this.form.zip_code = this.zip_code;
    },
    form: {
      handler: function(value) {
        this.$emit("input", value);
      },
      deep: true
    }
  }
};
</script>
