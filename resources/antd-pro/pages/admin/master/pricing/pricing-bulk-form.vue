<template>
  <div>
    <div v-if="hasSlotTrigger" @click="activateModal">
      <slot name="trigger"></slot>
    </div>
    <a-button v-else @click="activateModal">{{ title }}</a-button>

    <a-modal
      v-model="visible"
      @ok="handleOk"
      @cancel="defaultsAction"
      :okText="okText"
      :cancelText="cancelText"
      :centered="true"
      :confirm-loading="confirmLoading"
      :width="'60%'"
      :after-close="defaultsAction"
    >
      <template slot="title">
        <h2>{{ title }}</h2>
      </template>

      <a-form-model layout="vertical">
        <!-- Origin -->
        <a-row type="flex" :gutter="[10, 10]">
          <a-col :span="8">
            <a-form-model-item>
              <template slot="label">
                <h4>Kota Asal</h4>
              </template>
              <a-select
                v-model="form.origin_regency"
                :auto-focus="true"
                :allow-clear="true"
                show-search
                placeholder="Pilih Kota Asal"
                option-filter-prop="children"
                style="width: 100%"
                :filter-option="filterOption"
                @focus="getGeo(true,'regency',{origin: 1})"
                :loading="origin_regencies.length === 0"
              >
                <a-select-option
                  :value="-1"
                >
                  {{ 'Jabodetabek' }}
                </a-select-option>
                <a-select-option
                  v-for="(regency, index) in origin_regencies"
                  :key="index"
                  :value="regency.id"
                >
                  {{ regency.name }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </a-col>
        </a-row>

        <!-- Destination -->
        <a-row type="flex" :gutter="[10, 10]">
          <a-col :span="8">
            <a-form-model-item>
              <template slot="label">
                <h4>Provinsi Tujuan</h4>
              </template>
              <a-select
                v-model="form.destination_province_id"
                :width="'100%'"
                show-search
                placeholder="Pilih Provinsi Tujuan"
                option-filter-prop="children"
                style="width: 100%"
                :filter-option="filterOption"
                :disabled="!form.origin_regency"
                @focus="getGeo(false, 'province')"
                :allow-clear="true"
                :loading="destination_provinces.length === 0"
              >
                <a-select-option
                  v-for="(province, index) in destination_provinces"
                  :key="index"
                  :value="province.id"
                >
                  {{ province.name }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item>
              <template slot="label">
                <h4>Kota/kab Tujuan</h4>
              </template>
              <a-select
                v-model="form.destination_regency_id"
                :width="'100%'"
                show-search
                placeholder="Pilih Kota/kab Tujuan"
                option-filter-prop="children"
                style="width: 100%"
                :filter-option="filterOption"
                :disabled="!form.destination_province_id"
                @focus="getGeo(false, 'regency', { province_id: form.destination_province_id })"
                :allow-clear="true"
                :loading="destination_regencies.length === 0"
              >
                <a-select-option
                  v-for="(regency, index) in destination_regencies"
                  :key="index"
                  :value="regency.id"
                >
                  {{ regency.name }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item>
              <template slot="label">
                <h4>Kecamatan Tujuan</h4>
              </template>
              <a-select
                v-model="form.destination_district_id"
                :width="'100%'"
                show-search
                placeholder="Pilih Kecamatan Tujuan"
                option-filter-prop="children"
                style="width: 100%"
                :filter-option="filterOption"
                :disabled="!form.destination_regency_id"
                @focus="getGeo(false, 'district', { regency_id: form.destination_regency_id })"
                :allow-clear="true"
                :loading="destination_districts.length === 0"
                @blur="districtBlur"
              >
                <a-select-option
                  v-for="(district, index) in destination_districts"
                  :key="index"
                  :value="district.id"
                >
                  {{ district.name }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </a-col>
        </a-row>

        <!-- Sub district -->
        <a-row v-if="form.destination_district_id " type="flex" :gutter="[10, 10]">
          <a-col :span="16">
            <a-form-model-item>
              <template slot="label">
                <h4>Kelurahan Tujuan</h4>
              </template>
              <a-space direction="vertical" style="width: 100%">
                <a-checkbox
                  :checked="checkAll"
                  @change="onCheckAllChange"
                >
                  Pilih Semua
                </a-checkbox>
                <a-select
                  v-if="!checkAll"
                  mode="tags"
                  placeholder="Pilih Kelurahan Tujuan"
                  style="width: 100%"
                  @focus="getGeo(false, 'sub_district', { district_id: form.destination_district_id })"
                  @change="setSubDistricts"
                >
                  <a-select-option
                    v-for="(sub_district, index) in destination_sub_districts"
                    :key="index"
                    :value="`${sub_district.id}`"
                  >
                    {{ sub_district.name }}
                  </a-select-option>
                </a-select>
              </a-space>
            </a-form-model-item>
          </a-col>
        </a-row>

        <!-- Notes & Service -->
        <a-row v-if="form.destination_sub_districts.length > 0" type="flex" :gutter="[10, 10]">
          <a-col :span="8">
            <a-form-model-item>
              <template slot="label">
                <h4>Metode Pengiriman</h4>
              </template>
              <a-input placeholder="'Services'" :value="'Trawlpack'" disabled/>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item>
              <template slot="label">
                <h4>Estimasi</h4>
              </template>
              <a-textarea v-model="form.notes"></a-textarea>
            </a-form-model-item>
          </a-col>
        </a-row>

        <!-- Price List -->
        <a-row v-if="form.destination_sub_districts.length > 0" type="flex" :gutter="[10, 10]">
          <a-col :span="8">
            <a-form-model-item>
              <template slot="label">
                <h4>Tarif 0 - 10 Kg</h4>
              </template>
              <a-input-number v-model="form.tier_1"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item>
              <template slot="label">
                <h4>Tarif 11 - 30 Kg</h4>
              </template>
              <a-input-number v-model="form.tier_2"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item>
              <template slot="label">
                <h4>Tarif 31 - 50 Kg</h4>
              </template>
              <a-input-number v-model="form.tier_3"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item>
              <template slot="label">
                <h4>Tarif 51 - 100 Kg</h4>
              </template>
              <a-input-number v-model="form.tier_4"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item>
              <template slot="label">
                <h4>Tarif 101 - 1.000 Kg</h4>
              </template>
              <a-input-number v-model="form.tier_5"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item>
              <template slot="label">
                <h4>Tarif > 1.000 Kg</h4>
              </template>
              <a-input-number v-model="form.tier_6"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item>
              <template slot="label">
                <h4>Tarif > 3.000 Kg</h4>
              </template>
              <a-input-number v-model="form.tier_7"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item>
              <template slot="label">
                <h4>Tarif > 5.000 Kg</h4>
              </template>
              <a-input-number v-model="form.tier_8"></a-input-number>
            </a-form-model-item>
          </a-col>
        </a-row>
      </a-form-model>
    </a-modal>
  </div>
</template>
<script>
export default {
  data() {
    return {
      checkAll: true,
      visible: false,
      loading: false,
      confirmLoading: false,
      rules: {
        origin_regency_id: [
          { required: true },
        ]
      },
      form: {
        origin_regency: null,
        destination_province_id: null,
        destination_regency_id: null,
        destination_district_id: null,
        destination_sub_districts: [],
        service_code: 'tps',
        desc: null,
        tier_1: null,
        tier_2: null,
        tier_3: null,
        tier_4: null,
        tier_5: null,
        tier_6: null,
        tier_7: null,
        tier_8: null,
        notes: null
      },
    }
  },
  props: {
    title: {
      type: String,
      default: 'Modal Title',
    },
    isUpdate: {
      type: Boolean,
      default: false,
    },
  },
  methods: {
    onCheckAllChange(e) {
      Object.assign(this, {
        checkAll: e.target.checked,
      });
      this.form.destination_sub_districts = this.checkAll ? [-1] : []
    },
    defaultsAction() {
      this.confirmLoading = false;
      this.visible = false;
      this.form = {
        origin_regency: null,
        destination_province_id: null,
        destination_regency_id: null,
        destination_district_id: null,
        destination_sub_districts: [],
        service_code: 'tps',
        desc: null,
        tier_1: null,
        tier_2: null,
        tier_3: null,
        tier_4: null,
        tier_5: null,
        tier_6: null,
        tier_7: null,
        tier_8: null,
        notes: null
      };
    },
    activateModal() {
        if (this.data) {
          this.fillForm();
        }
        this.visible = true;
    },
    handleOk() {
      this.confirmLoading = true;
      console.log('oke')
    },
    setSubDistricts(value) {
      this.form.destination_sub_districts = value.map(x => +x);
    },
    districtBlur() {
      this.getGeo(false, 'sub_district', { district_id: this.form.destination_district_id });
      this.form.destination_sub_districts = this.checkAll ? [-1] : [];
    },
    handleFocus() {
      console.log('focus');
    },
    filterOption(input, option) {
      return (
        option.componentOptions.children[0].text.toLowerCase().indexOf(input.toLowerCase()) >= 0
      );
    },

  },
  computed: {
    hasSlotTrigger() {
      return !!this.$slots.trigger;
    },
    okText() {
      return this.isUpdate ? 'Update' : 'Simpan';
    },
    cancelText() {
      return 'Batal';
    }
  }
}
</script>
