<template>
  <a-badge
    v-if="packageOrderTypeTitle"
    :count="packageOrderTypeTitle"
    :class="['trawl-badge-success']"
  />
  <a-badge v-else :count="'Not Set'" :class="['trawl-badge-success']" />
</template>
<script>
import { getPackageOrderType } from "../../functions/packageStatus";
export default {
  props: {
    package: {
      type: Object,
      default: () => {},
    },
  },
  computed: {
    packageOrderTypeTitle() {
      return this.packageOrderType?.title;
    },
    packageOrderType() {
      return getPackageOrderType(this.package?.type);
    },
  },
};
</script>
