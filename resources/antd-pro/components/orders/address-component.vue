<template>
  <div>
    <slot name="title" v-if="hasTitleSlot"></slot>
    <h5 v-else>{{ receiver ? "Penerima" : "Pengirim" }}</h5>
    <h5>
      <b>Faris Ferdian Akbar</b>
    </h5>
    <h5 class="trawl-text-normal">
      085694455935
    </h5>
    <p>
      Jl. Soekarno No.1, Kel. Indarung, Kec. Lubuk Kilangan, Padang, Sumatera
      Barat.
    </p>
  </div>
</template>
<script>
export default {
  props: ["record", "receiver", "title"],
  computed: {
    hasTitleSlot() {
      return !!this.$slots.title;
    }
  }
};
</script>
