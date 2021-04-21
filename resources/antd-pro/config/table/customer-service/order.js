import moment from "moment";

export default [
  {
    title: "No",
    dataIndex: "number"
    // customRender: (text, row, index) => {
    //   return {
    //     children: text,
    //     attrs: {
    //       rowSpan: 2
    //     }
    //   };
    // }
  },

  {
    title: "ID Order",
    customRender: (text, row, index) => {
      return {
        children: row.packages[0]?.code.content
      };
    }
  },
  {
    title: "Armada",
    key: "transporter",
    customRender: (text, row, index) => {
      return {
        children: row.packages[0]?.transporter_type
      };
    }
  },
  {
    title: "Lokasi Penjemputan",
    key: "sender_address",
    customRender: (text, row, index) => {
      return {
        children: row.packages[0]?.sender_address
      };
    }
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
          colSpan: 2
        },
        children: moment(text).format("ddd, DD MMM YYYY HH:mm:ss")
      };
    }
  }
];
