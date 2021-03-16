<template>
  <div>
    <div v-if="record.status == 'created'">
      <a-badge status="warning" text=""></a-badge>
      <span :class="['trawl-status-warning']">Menunggu Konfirmasi Order</span>
    </div>
  </div>
</template>
<script>
export default {
  props: ["record"]
};
</script>
