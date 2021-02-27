<template>
  <div>
    <h2>Info Owner</h2>
    <a-row type="flex" :gutter="[10, 10]">
      <a-col :span="8">
        <h3>Nama Lengkap</h3>
        <a-input v-model="form.name"></a-input>
      </a-col>
      <a-col :span="8">
        <h3>Nomor Hp</h3>
        <a-input v-model="form.phone"></a-input>
      </a-col>
      <a-col :span="8">
        <h3>Email</h3>
        <a-input v-model="form.email"></a-input>
      </a-col>
      <a-col :span="8">
        <h3>Username</h3>
        <a-input v-model="form.username"></a-input>
      </a-col>
      <a-col :span="8">
        <h3>Password</h3>
        <a-input-password v-model="form.password"></a-input-password>
      </a-col>
      <a-col :span="8">
        <h3>Ulangi Kata Sandi</h3>
        <a-input-password
          v-model="form.password_confirmation"
        ></a-input-password>
      </a-col>
      <a-col :span="16">
        <h3>Alamat Lengkap</h3>
        <a-textarea v-model="form.address" :rows="5"></a-textarea>
      </a-col>
    </a-row>
    <a-row type="flex" :gutter="[10, 10]">
      <a-col :span="8">
        <h3>Foto Owner</h3>
        <a-button icon="plus" size="large" type="primary"></a-button>
      </a-col>
    </a-row>
  </div>
</template>

<script>
export default {
  data() {
    return {
      form: {
        name: null,
        phone: null,
        email: null,
        username: null,
        password: null,
        password_confirmation: null,
        address: null,
        photo: null
      }
    };
  }
};
</script>
