<template>
  <div>
    <edit-button @click="visible = true"></edit-button>
    <a-modal
      v-model="visible"
      ok-text="Simpan"
      cancel-text="Batal"
      @ok="handleOk"
      @cancel="handleCancel"
      maskClosable
      closable
      :width="720"
    >
      <template slot="title">
        {{ title }}
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

      <a-form>
        <a-row type="flex" :gutter="[10, 10]">
          <a-col :span="6">
            <span>Nama Pegawai</span>
            <a-form-item>
              <a-input v-model="form.name" @input="handleInput"></a-input>
            </a-form-item>
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
            <a-select :default-value="form.role" v-model="form.role">
              <a-select-option v-for="role in roles" :key="role">
                {{ role }}
              </a-select-option>
            </a-select>
          </a-col>
        </a-row>
      </a-form>
    </a-modal>
  </div>
</template>
<script>
import { message } from "ant-design-vue";
import EditButton from "../../../../components/button/edit-button.vue";
export default {
  components: {
    EditButton
  },
  data() {
    return {
      visible: false,
      loading: false,
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

  computed: {
    employeeParent() {
      return this.getParent("master-employee");
    }
  },

  methods: {
    handleCancel() {
      this.form = { ...this.employeeData };
      this.visible = false;
    },
    handleOk() {
      this.$http
        .patch(this.routeUri(this.getRoute()), this.form)
        .then(resp => this.onSuccessResponse(resp))
        .catch(err => this.onErrorResponse(err));
    },
    onSuccessResponse(resp) {
      this.employeeParent.getItems();
      this.visible = false;
    },
    onErrorResponse(error) {
      let message = error.response.data.data;
      message = _.head(message[_.head(_.keys(message))]);
      this.$notification.error({
        message: error.response.data.message + " " + message
      });
    },
    handleInput() {}
  },
  created() {
    this.form = { ...this.employeeData };
  }
};
</script>
