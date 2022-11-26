import moment from "moment";

export default [
  {
    title: "No",
    dataIndex: "number",
    colSpan: 1,
  },

  {
    title: "ID Order",
    colSpan: 3,
    customRender: (text, row, index) => {
      let temp = row.package_multi.map((element) => element.code);
      return {
        children: temp.join(" "),
      };
    },
  },
  {
    title: "Type",
    colSpan: 2,
    customRender: (text, row, index) => {
      return {
        children: row.package.order_type,
      };
    },
  },
  {
    title: "Armada",
    key: "transporter",
    colSpan: 2,
    customRender: (text, row, index) => {
      return {
        children: row.package.transporter_type,
      };
    },
  },
  {
    title: "Lokasi Penjemputan",
    key: "sender_address",
    colSpan: 3,
    customRender: (text, row, index) => {
      return {
        children: row.package.sender_address,
      };
    },
  },
  // {
  //   title: "metode Pengiriman",
  //   key: "shipping_method",
  //   colSpan: 2,
  //   customRender: (text, row, index) => {
  //     return {
  //       children: row.shipping_method,
  //     };
  //   },
  // },
  {
    title: "Jenis Order",
    key: "order_mode",
    colSpan: 2,
    customRender: (text, row, index) => {
      return {
        children: row.order_mode,
      };
    },
  },
  {
    title: "Tanggal Order",
    dataIndex: "created_at",
    key: "created_at",
    // width: "100px",
    colSpan: 2,
    align: "center",
    customRender: (text, row, index) => {
      return {
        attrs: {
          colSpan: 2,
        },
        children: moment(text).format("ddd, DD MMM YYYY HH:mm:ss"),
      };
    },
  },
  {
    title: "Biaya Penjemputan",
    key: "service_price",
    colSpan: 2,
    customRender: (text, row, index) => {
      let result = 0;
      row.prices.forEach((el) => {
        if (el.type == "delivery" && el.description == "pickup") {
          result = el.amount;
        }
      });
      return {
        children: row.package.service_price,
      };
    },
  },
];
