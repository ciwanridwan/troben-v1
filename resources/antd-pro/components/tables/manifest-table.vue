<template>
  <trawl-table :columns="manifestColumns" :dataSource="dataSource">
    <template slot="manifest" slot-scope="{ record }">
      <a-space direction="vertical">
        <span v-if="record.code">
          {{ record.code.content }}
        </span>
        <span
          >Jumlah Kg :
          <span class="trawl-text-bold"
            >{{ record.weight_borne_total }} Kg</span
          ></span
        >
        <span> </span>
      </a-space>
    </template>
    <template slot="origin_partner" slot-scope="{ record }">
      <template v-if="record.origin_partner">
        <span class="trawl-text-bold"> {{ record.origin_partner.code }}</span>
        <p>{{ record.origin_partner.address }}</p>
      </template>
    </template>
    <template slot="partner" slot-scope="{ record }">
      <template v-if="record.partner">
        <span class="trawl-text-bold"> {{ record.partner.code }}</span>
        <p>{{ record.partner.address }}</p>
      </template>
    </template>
    <span slot="expandedRowRender" slot-scope="{ record }">
      <a-row type="flex" justify="space-between">
        <a-col :span="8">
          <manifest-status :record="record"></manifest-status>
        </a-col>
      </a-row>
    </span>
  </trawl-table>
</template>
<script>
import trawlTable from "../trawl-table.vue";
import manifestColumns from "../../config/table/home/trawl-manifest";
import ManifestStatus from "../manifest-status.vue";

export default {
  data() {
    return {
      manifestColumns
    };
  },
  props: {
    dataSource: Array,
    default: () => {
      return [];
    }
  },
  components: { trawlTable, ManifestStatus }
};
</script>
