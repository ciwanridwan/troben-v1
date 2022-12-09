<template>
  <a-form-model
    ref="formRules"
    :hideRequiredMark="true"
    :model="value"
    :rules="rules"
  >
    <a-row type="flex" :gutter="[12, 12]">
      <a-col :span="6">
        <a-form-model-item label="Deskripsi Barang" prop="desc">
          <a-select
            v-model="value.name"
            size="large"
            placeholder="Deskripsi Barang"
            @change="toggleType"
          >
            <a-select-option
              v-for="(item, index) in items"
              :key="index"
              :value="item.toLowerCase()"
            >
              {{ item }}
            </a-select-option>
          </a-select>
          <!-- <a-input
            size="large"
            v-model="value.desc"
            placeholder="Deskripsi Barang"
          ></a-input> -->
        </a-form-model-item>
      </a-col>
      <a-col v-show="value.name == 'lainnya'" :span="6">
        <a-form-model-item label="Detail Barang" prop="desc">
          <a-input
            type="text"
            size="large"
            v-model="item_desc"
            placeholder="Detail Barang"
          ></a-input>
        </a-form-model-item>
      </a-col>
    </a-row>

    <a-row v-show="value.name != 'motor'" type="flex" :gutter="[12, 12]">
      <a-col :span="6">
        <a-form-model-item label="Panjang (cm)" prop="length">
          <a-input
            type="number"
            size="large"
            v-model.number="value.length"
            placeholder="Panjang (cm)"
          ></a-input>
        </a-form-model-item>
      </a-col>
      <a-col :span="6">
        <a-form-model-item label="Lebar (cm)" prop="width">
          <a-input
            type="number"
            size="large"
            v-model.number="value.width"
            placeholder="Lebar (cm)"
          ></a-input>
        </a-form-model-item>
      </a-col>
      <a-col :span="6">
        <a-form-model-item label="Tinggi (cm)" prop="height">
          <a-input
            type="number"
            size="large"
            v-model.number="value.height"
            placeholder="Tinggi (cm)"
          ></a-input>
        </a-form-model-item>
      </a-col>
    </a-row>
    <a-row v-show="value.name != 'motor'" type="flex" :gutter="[12, 12]">
      <a-col :span="6">
        <a-form-model-item label="Berat (kg)" prop="weight">
          <a-input
            type="number"
            size="large"
            v-model.number="value.weight"
            placeholder="Berat (kg)"
          ></a-input>
        </a-form-model-item>
      </a-col>
      <a-col :span="6">
        <a-form-model-item label="Jumlah Paket" prop="qty">
          <a-input
            type="number"
            size="large"
            v-model.number="value.qty"
            placeholder="Jumlah Paket"
          ></a-input>
        </a-form-model-item>
      </a-col>
      <a-col :span="6">
        <a-form-model-item label="Perkiraan Harga Barang" prop="price">
          <a-input
            type="number"
            size="large"
            v-model.number="value.price"
            placeholder="Perkiraan Harga Barang"
          ></a-input>
        </a-form-model-item>
      </a-col>
    </a-row>
    <!-- form motor -->

    <a-row v-show="value.name == 'motor'" type="flex" :gutter="[12, 12]">
      <a-col :span="9">
        <a-form-model-item label="Jenis Motor" prop="moto_type">
          <a-select
            v-model="value.moto_type"
            size="large"
            placeholder="Deskripsi Barang"
          >
            <a-select-option
              v-for="(item, index) in motorType"
              :key="index"
              :value="item.toLowerCase()"
            >
              {{ item }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
      </a-col>
      <a-col :span="9">
        <a-form-model-item label="Merek Motor" prop="moto_merk">
          <a-input
            v-model="value.moto_merk"
            type="text"
            size="large"
            placeholder="Merek Motor"
          ></a-input>
        </a-form-model-item>
      </a-col>
    </a-row>
    <a-row v-show="value.name == 'motor'" type="flex" :gutter="[12, 12]">
      <a-col :span="9">
        <a-form-model-item label="CC Motor" prop="moto_cc">
          <a-select
            v-model="value.moto_cc"
            size="large"
            placeholder="Deskripsi Barang"
          >
            <a-select-option
              v-for="(item, index) in ccMotor"
              :key="index"
              :value="item.type"
            >
              {{ item.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
      </a-col>
      <a-col :span="9">
        <a-form-model-item label="Tahun Motor" prop="moto_year">
          <a-input
            v-model="value.moto_year"
            type="number"
            size="large"
            placeholder="Tahun Motor"
          ></a-input>
        </a-form-model-item>
      </a-col>
    </a-row>
  </a-form-model>
</template>
<script>
export default {
  data() {
    return {
      rules: {
        name: [{ required: true }],
        order_type: [{required: true}],
        desc: [{ required: true }],
        length: [{ required: true }],
        width: [{ required: true }],
        height: [{ required: true }],
        weight: [{ required: true }],
        qty: [{ required: true }],
        price: [{ required: true }],
        moto_cc: [{ required: true }],
        moto_type: [{ required: true }],
        moto_merk: [{ required: true }],
        moto_year: [{ required: true }],
      },
      items: [
        "Pakaian",
        "Kosmetik",
        "Aksesoris",
        "Makanan Non Frozen",
        "Motor",
        "Elektronik",
        "Parabotan",
        "Lainnya",
      ],
      ccMotor: [
        {
          name: "100 CC - 150 CC",
          type: 150,
        },
        {
          name: "151 CC - 250 CC",
          type: 250,
        },
        {
          name: ">250 CC",
          type: 999,
        },
      ],
      motorType: ["Matic", "Kopling", "Gigi"],
      item_desc: "",
      isMotor: false,
    };
  },
  props: {
    onChange: {
      type: Function,
      default: () => {},
    },
    defaultValue: {
      type: Object,
      default: () => {},
    },
    value: {
      type: Object,
      default: () => {
        return {
          name: null,
          order_type: null,
          desc: null,
          length: null,
          width: null,
          height: null,
          weight: null,
          qty: null,
          price: null,
          moto_cc: null,
          moto_type: null,
          moto_merk: null,
          moto_year: null,
          order_type: null
        };
      },
    },
  },
  watch: {
    value: {
      handler: function (value) {
        if (value.name == "lainnya") {
          value.desc = this.item_desc;
          value.order_type="other"
        } else if (value.name == "motor") {
          value.desc = "bike";
          value.order_type= "bike"
        } else {
          value.desc = value.name;
        }
        // this.toggleType(value.name);
        this.onChange(value);
        this.$emit("change", value);
      },
      deep: true,
    },
  },
  methods: {
    toggleType(type) {
      if (type == "motor") {
        this.isMotor = true;
        this.value.length = 0;
        this.value.width = 0;
        this.value.height = 0;
        this.value.weight = 0;
        this.value.qty = 0;
        this.value.price = 0;
        this.value.moto_cc = null;
        this.value.moto_type = null;
        this.value.moto_merk = null;
        this.value.moto_year = null;
        this.value.order_type = 'bike';
      } else {
        this.isMotor = false;
        this.value.length = null;
        this.value.width = null;
        this.value.height = null;
        this.value.weight = null;
        this.value.qty = null;
        this.value.price = null;
        this.value.moto_cc = 0;
        this.value.moto_type = 0;
        this.value.moto_merk = 0;
        this.value.moto_year = 0;
        this.value.order_type = 'other'
      }
    },
  },
};
</script>
