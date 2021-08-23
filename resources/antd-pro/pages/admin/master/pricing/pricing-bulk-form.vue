<template>
  <div>
    <div v-if="hasSlotTrigger" @click="activateModal">
      <slot name="trigger"></slot>
    </div>
    <a-button v-else @click="activateModal">{{ title }}</a-button>

    <a-modal
      v-if="visible"
      v-model="visible"
      @ok="showConfirm"
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

      <a-form-model
        ref="ruleForm"
        :model="form"
        :rules="rules"
        layout="vertical"
      >
        <!-- Origin -->
        <a-row type="flex" :gutter="[10, 10]">
          <a-col :span="8">
            <a-form-model-item label="Kota Asal" prop="origin_regency">
              <a-select
                :auto-focus="true"
                :allow-clear="true"
                show-search
                placeholder="Pilih Kota Asal"
                option-filter-prop="children"
                style="width: 100%"
                :filter-option="filterOption"
                @focus="getGeo(true,'regency',{origin: 1})"
                @change="originChange"
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
            <a-form-model-item label="Provinsi Tujuan" prop="destination_province_id">
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
                @change="() => {this.form.destination_regency_id = undefined; this.form.destination_district_id = undefined; this.form.destination_sub_districts = []; getGeo(false, 'regency', { province_id: form.destination_province_id });}"
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
            <a-form-model-item label="Kota/kab Tujuan" prop="destination_regency_id">
              <a-select
                v-model="form.destination_regency_id"
                :width="'100%'"
                show-search
                placeholder="Pilih Kota/kab Tujuan"
                option-filter-prop="children"
                style="width: 100%"
                :filter-option="filterOption"
                :disabled="!form.destination_province_id"
                @change="() => {this.form.destination_district_id = undefined; this.form.destination_sub_districts = []; getGeo(false, 'district', { regency_id: form.destination_regency_id });}"
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
            <a-form-model-item label="Kecamatan Tujuan" prop="destination_district_id">
              <a-select
                v-model="form.destination_district_id"
                :width="'100%'"
                show-search
                placeholder="Pilih Kecamatan Tujuan"
                option-filter-prop="children"
                style="width: 100%"
                :filter-option="filterOption"
                :disabled="!form.destination_regency_id"
                :allow-clear="true"
                :loading="destination_districts.length === 0"
                @change="districtChange"
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
        <a-row v-if="form.destination_district_id" type="flex" :gutter="[10, 10]">
          <a-col :span="16">
            <a-form-model-item label="Kelurahan Tujuan" prop="destination_sub_districts">
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
                  :filter-option="filterOption"
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
            <a-form-model-item label="Metode Pengiriman">
              <a-input placeholder="'Services'" :value="'Trawlpack'" disabled/>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item label="Estimasi">
              <a-textarea v-model="form.notes"></a-textarea>
            </a-form-model-item>
          </a-col>
        </a-row>

        <!-- Price List -->
        <a-row v-if="form.destination_sub_districts.length > 0" type="flex" :gutter="[10, 10]">
          <a-col :span="8">
            <a-form-model-item label="Tarif 0 - 10 Kg" prop="tier_1">
              <a-input-number v-model="form.tier_1"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item label="Tarif 11 - 30 Kg" prop="tier_2">
              <a-input-number v-model="form.tier_2"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item label="Tarif 31 - 50 Kg" prop="tier_3">
              <a-input-number v-model="form.tier_3"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item label="Tarif 51 - 100 Kg" prop="tier_4">
              <a-input-number v-model="form.tier_4"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item label="Tarif 101 - 1.000 Kg" prop="tier_5">
              <a-input-number v-model="form.tier_5"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item label="Tarif > 1.000 Kg" prop="tier_6">
              <a-input-number v-model="form.tier_6"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item label="Tarif > 3.000 Kg" prop="tier_7">
              <a-input-number v-model="form.tier_7"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item label="Tarif > 5.000 Kg" prop="tier_8">
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
      confirmLoading: false,
      rules: {
        origin_regency: [{ required: true, message: 'Kota asal wajib diisi' }],
        destination_province_id: [{ required: true, message: 'Provinsi tujuan wajib diisi' }],
        destination_regency_id: [{ required: true, message: 'Kota tujuan wajib diisi' }],
        destination_district_id: [{ required: true, message: 'Kecamatan tujuan wajib diisi' }],
        destination_sub_districts: [{ required: true, message: 'Kelurahan tujuan wajib diisi' }],
        tier_1: [{ required: true, message: 'Harga tidak boleh kosong' }, {type: 'number', message: 'Mohon kerjasamanya, harus angka.'}],
        tier_2: [{ required: true, message: 'Harga tidak boleh kosong' }, {type: 'number', message: 'Mohon kerjasamanya, harus angka.'}],
        tier_3: [{ required: true, message: 'Harga tidak boleh kosong' }, {type: 'number', message: 'Mohon kerjasamanya, harus angka.'}],
        tier_4: [{ required: true, message: 'Harga tidak boleh kosong' }, {type: 'number', message: 'Mohon kerjasamanya, harus angka.'}],
        tier_5: [{ required: true, message: 'Harga tidak boleh kosong' }, {type: 'number', message: 'Mohon kerjasamanya, harus angka.'}],
        tier_6: [{ required: true, message: 'Harga tidak boleh kosong' }, {type: 'number', message: 'Mohon kerjasamanya, harus angka.'}],
        tier_7: [{ required: true, message: 'Harga tidak boleh kosong' }, {type: 'number', message: 'Mohon kerjasamanya, harus angka.'}],
        tier_8: [{ required: true, message: 'Harga tidak boleh kosong' }, {type: 'number', message: 'Mohon kerjasamanya, harus angka.'}],
      },
      form: {
        origin_regency: undefined,
        destination_province_id: undefined,
        destination_regency_id: undefined,
        destination_district_id: undefined,
        destination_sub_districts: [],
        service_code: 'tps',
        tier_1: 0,
        tier_2: 0,
        tier_3: 0,
        tier_4: 0,
        tier_5: 0,
        tier_6: 0,
        tier_7: 0,
        tier_8: 0,
        notes: undefined
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
      this.setAllSubDistricts()
    },
    defaultsAction() {
      this.confirmLoading = false;
      this.visible = false;
      this.form = {
        origin_regency: undefined,
        destination_province_id: undefined,
        destination_regency_id: undefined,
        destination_district_id: undefined,
        destination_sub_districts: [],
        service_code: 'tps',
        tier_1: 0,
        tier_2: 0,
        tier_3: 0,
        tier_4: 0,
        tier_5: 0,
        tier_6: 0,
        tier_7: 0,
        tier_8: 0,
        notes: undefined
      };
    },
    activateModal() {
        if (this.data) {
          this.fillForm();
        }
        this.visible = true;
    },
    showConfirm() {
      this.$refs.ruleForm.validate(valid => {
        if (valid) {
          this.$confirm({
            title: 'Udah yakin bung?',
            content: 'Setelah klik tombol "OK", data yang anda input akan disimpan coi.',
            okText: 'Yakin!',
            cancelText: 'Cek lagi.',
            onOk: this.updatePrice,
            onCancel() {},
          });
        } else {
          return false;
        }
      });
    },
    async updatePrice() {
      this.actionModalButton();
      await this.submitForm();
      this.actionModalButton(false);
      this.$notification.success({
        message: "Sukses update ongkir!"
      });
      this.visible = false;

    },
    async submitForm() {
      let uri = "admin.master.pricing.district.bulk";
      try {
        const { data } = await this.$http
          .post(this.routeUri(uri), this.form)
        if (data.error) {
          this.$notification.error({
            message: `${data.code}`,
            description: data.message,
          })
        }
        this.$emit('update');
      } catch (e) {
        this.$notification.error({
          message: 'something went wrong'
        });
      }
    },
    actionModalButton(isDisabled = true) {
      Object.assign(this, {
        confirmLoading: isDisabled,
        closeable: !isDisabled,
        maskClosable: !isDisabled,
        okButtonProps: {
          props: {
            disabled: isDisabled,
          },
        },
        cancelButtonProps: {
          props: {
            disabled: isDisabled
          }
        }
      });
    },
    setSubDistricts(value) {
      this.form.destination_sub_districts = value.map(x => +x);
      if (this.form.destination_sub_districts.length === this.destination_sub_districts.length) {
        this.checkAll = true;
      }
    },
    async districtChange() {
      this.form.destination_sub_districts = [];
      this.checkAll = true;
      await this.getGeo(false, 'sub_district', { district_id: this.form.destination_district_id });
      this.setAllSubDistricts();
    },
    filterOption(input, option) {
      return (
        option.componentOptions.children[0].text.toLowerCase().indexOf(input.toLowerCase()) >= 0
      );
    },
    setAllSubDistricts() {
      let data = this.destination_sub_districts.map(sub_district => sub_district.id);
      this.form.destination_sub_districts = this.checkAll ? data : [];
    },
    setAllRegencies() {
      let data = this.origin_regencies.map(regency => regency.id);
      this.form.origin_regency = this.checkAll ? data : [];
    },
    originChange(value) {
      this.form.destination_province_id = undefined;
      this.form.destination_regency_id = undefined;
      this.form.destination_district_id = undefined;
      this.form.destination_sub_districts = [];
      if (value === -1) {
        this.setAllRegencies();
      } else {
        this.form.origin_regency = value;
      }
      this.getGeo(false, 'province');
    }
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
  },
  mounted() {
    this.visible = true;
    this.$nextTick(() => {
      this.visible = false;
    });
  }
}
</script>
