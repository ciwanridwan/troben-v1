<template>
  <div>
    <h2>Lokasi Mitra</h2>
    <a-form-model ref="ruleForm" :rules="rules" :model="form">
      <a-row type="flex" :gutter="[10, 10]">
        <a-col :span="8">
          <trawl-input label="Provinsi">
            <template slot="input">
              <a-form-model-input ref="province" props="geo.province">
                <a-select
                  show-search
                  :filter-option="filterOption"
                  v-model="form.geo.province"
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
              </a-form-model-input>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Kota / Kabupaten">
            <template slot="input">
              <a-form-model-input ref="regency" props="geo.regency">
                <a-select
                  show-search
                  :filter-option="filterOption"
                  v-model="form.geo.regency"
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
              </a-form-model-input>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Kecamatan">
            <template slot="input">
              <a-form-model-input ref="geo.district" props="geo.district">
                <a-select
                  show-search
                  :filter-option="filterOption"
                  v-model="form.geo.district"
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
              </a-form-model-input>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Kelurahan">
            <template slot="input">
              <a-form-model-input ref="sub_district" props="geo.sub_district">
                <a-select
                  show-search
                  :filter-option="filterOption"
                  v-model="form.geo.sub_district"
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
              </a-form-model-input>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Kode Pos">
            <template slot="input">
              <a-form-model-item ref="zip_code" prop="geo.zip_code">
                <a-input-number
                  v-model="form.geo.zip_code"
                  disabled
                ></a-input-number>
              </a-form-model-item>
            </template>
          </trawl-input>
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
import trawlInput from "../../../../components/trawl-input.vue";
export default {
  components: { trawlInput },
  props: {
    geo: {
      type: Object
    }
  },
  data() {
    return {
      form: {
        geo: {
          regency: null,
          province: null,
          district: null,
          sub_district: null,
          zip_code: null
        },
        address: null
      },
      rules: {
        geo: {
          regency: [{ required: true }],
          province: [{ required: true }],
          district: [{ required: true }],
          sub_district: [{ required: true }]
        }
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
    },
    sub_districts() {
      return _.filter(this.geo.sub_districts, {
        district_id: this.form.geo.district
      });
    },
    zip_code() {
      let sub_district = _.find(this.geo.sub_districts, {
        id: this.form.geo.sub_district
      });
      return sub_district ? sub_district.zip_code : "";
    }
  },
  watch: {
    "form.geo.sub_district": function(value) {
      this.form.geo.zip_code = this.zip_code;
    }
  }
};
</script>
