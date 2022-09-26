export const planetChartData = (data) => {
  // console.log(data)
  return {
    type: "line",
    data: {
      // labels: Object.keys(data).map(k => `Day ${k}`),
      labels: Object.keys(data),
      datasets: [
        {
          label: "Jumlah Pendapatan",
          data: Object.values(data),
          // data:
          // ,
          backgroundColor: "rgba(71, 183,132,.5)",
          borderColor: "#47b784",
          borderWidth: 3,
        },
        // {
        //   label: "Hari ke 2",
        //   data: [0, 0, 1, 2, 79, 82, 27, 14],
        //   backgroundColor: "rgba(54,73,93,.5)",
        //   borderColor: "#36495d",
        //   borderWidth: 3
        // },
      ],
    },
    options: {
      responsive: true,
      lineTension: 1,
      scales: {
        yAxes: [
          {
            ticks: {
              beginAtZero: true,
              padding: 25,
              autoSkip: false,
            },
          },
        ],
      },
    },
  };
};

export default planetChartData;
