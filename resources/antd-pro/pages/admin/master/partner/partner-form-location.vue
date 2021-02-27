<template>
  <div>
    <h2>Lokasi Mitra</h2>
    <a-row type="flex" :gutter="[10, 10]">
      <a-col :span="8">
        <h3>Provinsi</h3>
        <a-select
          show-search
          :filter-option="filterOption"
          :default-value="form.geo.province"
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
      </a-col>
      <a-col :span="8">
        <h3>Kota / Kabupaten</h3>
        <a-select
          show-search
          :filter-option="filterOption"
          :default-value="form.geo.regency"
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
      </a-col>
      <a-col :span="8">
        <h3>Kecamatan</h3>
        <a-select
          show-search
          :filter-option="filterOption"
          :default-value="form.geo.district"
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
      </a-col>
      <a-col :span="8">
        <h3>Kelurahan</h3>
        <a-select
          show-search
          :default-value="form.geo.district"
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
      </a-col>
      <a-col :span="8">
        <h3>Kode Pos</h3>
        <a-input-number
          v-model="form.geo.zip_code"
          :default-value="zip_code"
          disabled
        ></a-input-number>
      </a-col>
      <a-col :span="16">
        <a-textarea v-model="form.address" :rows="5"></a-textarea>
      </a-col>
    </a-row>
  </div>
</template>
<script>
export default {
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
