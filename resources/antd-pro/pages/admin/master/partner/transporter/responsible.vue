<template>
  <div>
    <a-select
      v-model="responsible"
      show-search
      :filter-option="filterOptionMethod"
      @change="addResponsible"
    >
      <a-select-option
        v-for="regency in regenciesFiltered"
        :key="regency.id"
        :value="regency.id"
      >
        {{ regency.name }}
      </a-select-option>
    </a-select>
    <a-row type="flex" justify="space-between">
      <a-col :span="12" v-for="(item, index) in responsibles" :key="item.id">
        <a-row type="flex" align="middle">
          <a-col>
            <a-button
              type="link"
              icon="minus-circle"
              shape="circle"
              @click="removeResponsible(index)"
            ></a-button>
          </a-col>
          <a-col>
            {{ item.name }}
          </a-col>
        </a-row>
      </a-col>
    </a-row>
  </div>
</template>

<script>
export default {
  props: {
    regencies: {
      type: Array
    }
  },
  data() {
    return {
      responsible: null,
      responsibles: []
    };
  },
  computed: {
    regenciesFiltered() {
      return _.difference(this.regencies, this.responsibles);
    }
  },
  methods: {
    searchById(regency_id) {
      return _.find(this.regencies, { id: regency_id });
    },
    addResponsible(value) {
      this.responsibles.push(this.searchById(value));
      this.responsible = null;
    },
    removeResponsible(index) {
      this.responsibles.splice(index, 1);
    }
  }
};
</script>
