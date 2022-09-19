import moment from "moment";

export default [
  {
    title: "No",
    dataIndex: "number",
  },

  {
    title: "ID Order",
    customRender: (text, row, index) => {
      return {
        children: row.package.code.content,
      };
    },
  },
  {
    title: "Type",
    customRender: (text, row, index) => {
      return {
        children: row.package.order_type,
      };
    },
  },
  {
    title: "Armada",
    key: "transporter",
    customRender: (text, row, index) => {
      return {
        children: row.package.transporter_type,
      };
    },
  },
  {
    title: "Lokasi Penjemputan",
    key: "sender_address",
    customRender: (text, row, index) => {
      return {
        children: row.package.sender_address,
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
];
