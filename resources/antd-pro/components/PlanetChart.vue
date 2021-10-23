<template>
  <div>
    <canvas id="planet-chart"></canvas>
  </div>
</template>

<script>
import Chart from 'chart.js'
import planetChartData from '../data/planet-data'
export default {
  name: 'PlanetChart',
  props: ['itemsChart'],
  mounted() {
    this.$nextTick(function () {
      console.log('masukkkkkkkk')
      const ctx = document.getElementById('planet-chart');
      this.myChart = new Chart(ctx, planetChartData(this.itemsChart.data));
    })
  },
  data() {
    return {
      myChart: null
    }
  },

  watch: {
    itemsChart: function (newVal) {
      const ctx = document.getElementById('planet-chart').getContext('2d');
      this.myChart.destroy()
      this.myChart = new Chart(ctx, planetChartData(newVal.data));
    }
  }
}
</script>
