<template>
  <div>
    <edit-button @click="visible = true"></edit-button>
    <a-modal
      v-model="visible"
      @ok="handleOk"
      @cancel="handleCancel"
      maskClosable
      closable
      :width="720"
    >
      <template slot="title">
        {{ title }}
      </template>
      <template slot="footer">
        <a-button key="back" type="danger" ghost @click="handleCancel">
          Batal
        </a-button>
        <a-button key="submit" type="success" :loading="loading">
          Simpan
        </a-button>
      </template>
      <a-row type="flex" :gutter="[10, 10]">
        <a-col :span="8">
          <span>Jenis Mitra</span>
          <a-input v-model="form.partner.type" disabled></a-input>
        </a-col>
        <a-col :span="8">
          <span>Kode Mitra</span>
          <a-input v-model="form.partner.code" disabled></a-input>
        </a-col>
      </a-row>
      <a-row type="flex" :gutter="[10, 10]">
        <a-col :span="6">
          <span>Nama Pegawai</span>
          <a-input v-model="form.name" @input="handleInput"></a-input>
        </a-col>
        <a-col :span="6">
          <span>Nomor Hp</span>
          <a-input v-model="form.phone"></a-input>
        </a-col>
        <a-col :span="6">
          <span>Email</span>
          <a-input v-model="form.email"></a-input>
        </a-col>
        <a-col :span="6">
          <span>Jabatan</span>
          <a-select :default-value="form.role">
            <a-select-option v-for="role in roles" :key="role">
              {{ role }}
            </a-select-option>
          </a-select>
        </a-col>
      </a-row>
    </a-modal>
  </div>
</template>
<script>
import EditButton from "../../../../components/button/edit-button.vue";
export default {
  components: {
    EditButton
  },
  data() {
    return {
      form: {
        partner: {
          type: null,
          code: null
        },
        name: null,
        phone: null,
        email: null,
        role: null
      }
    };
  },
  props: {
    btnTitle: {
      type: String
    },
    visible: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: "Tambah Data Ongkir"
    },
    employeeData: {
      type: Object
    },
    roles: {
      type: Array,
      default: []
    }
  },

  methods: {
    handleCancel() {
      this.visible = false;
    },
    handleOk() {
      this.visible = false;
    },
    handleInput() {
      console.log(this.form);
    }
  },
  created() {
    this.form = this.employeeData;
  }
};
</script>
