<template>
  <div>
    <content-layout title="Armada">
      <template slot="head-tools">
        <a-row type="flex" justify="end">
          <a-col>
            <add-transporter-form></add-transporter-form>
          </a-col>
        </a-row>
      </template>
      <template slot="content">
        <a-table :columns="partnerTransporterColumns" style="margin-top:24px">
        </a-table>
      </template>
    </content-layout>
  </div>
</template>
<script>
import addTransporterForm from "./add-transporter-form.vue";
import partnerTransporterColumns from "../../../../../config/table/partner-transporter";
import ContentLayout from "../../../../../layouts/content-layout.vue";
export default {
  data() {
    return {
      partnerTransporterColumns
    };
  },
  components: { addTransporterForm, ContentLayout }
};
</script>
